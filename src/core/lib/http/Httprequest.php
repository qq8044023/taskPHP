<?php
namespace core\lib\http;

class Httprequest{
    public $method;             // HTTP method, e.g. "GET" or "POST"
    public $request_uri;        // original requested URI, with query string
    public $uri;                // path component of URI, without query string, after decoding %xx entities
    public $http_version;       // version from the request line, e.g. "HTTP/1.1"
    public $query_string;       // query string, like "a=b&c=d"
    public $headers;            // associative array of HTTP headers
    public $lc_headers;         // associative array of HTTP headers, with header names in lowercase
    public $content_stream;     // stream containing content of HTTP request (e.g. POST data)
    public $remote_addr;        // IP address of client, as string
    public $request_line;       // The HTTP request line exactly as it came from the client
    public $start_time;         // unix timestamp of initial request data, as float with microseconds
               
    // internal fields to track the state of reading the HTTP request
    private $cur_state = 0;
    private $header_buf = '';
    private $content_len = 0;
    private $content_len_read = 0;
    
    private $is_chunked = false;
    private $chunk_state = 0;
    private $chunk_len_remaining = 0;
    private $chunk_trailer_remaining = 0;
    private $chunk_header_buf = '';

    const READ_CHUNK_HEADER = 0;
    const READ_CHUNK_DATA = 1;
    const READ_CHUNK_TRAILER = 2;
    
    const READ_HEADERS = 0;
    const READ_CONTENT = 1;
    const READ_COMPLETE = 2;
        
    // fields used by HTTPServer to track other data along with the request
    public $socket;
    public $response;
    
    function __construct($socket){
        $this->socket = $socket;        
        $this->content_stream = fopen("data://text/plain,", 'r+b');
        
        $remote_name = stream_socket_get_name($socket, true);
        if ($remote_name){        
            $port_pos = strrpos($remote_name, ":");
            if ($port_pos){
                $this->remote_addr = substr($remote_name, 0, $port_pos);
            }else{
                $this->remote_addr = $remote_name;
            }
        }
    }
                            
    function cleanup(){
        fclose($this->content_stream);
        $this->content_stream = null;
    }
                            
    /* 
     * Reads a chunk of a HTTP request from a client socket.
     */
    function add_data($data){    
        switch ($this->cur_state){
            case static::READ_HEADERS:
                if (!$this->start_time){
                    $this->start_time = microtime(true);
                }
            
                $header_buf =& $this->header_buf;
            
                $header_buf .= $data;
                       
                if (strlen($header_buf) < 4){
                    break;
                }
                       
                $end_headers = strpos($header_buf, "\r\n\r\n", 4);
                if ($end_headers === false){
                    break;
                }         

                // parse HTTP request line    
                $end_req = strpos($header_buf, "\r\n"); 
                $this->request_line = substr($header_buf, 0, $end_req);
                $req_arr = explode(' ', $this->request_line, 3);

                $this->method = $req_arr[0];
                $this->request_uri = $req_arr[1];
                $this->http_version = $req_arr[2];    
                
                $parsed_uri = parse_url($this->request_uri);        
                $this->uri = urldecode($parsed_uri['path']);
                $this->query_string = @$parsed_uri['query'];              
                
                // parse HTTP headers
                $start_headers = $end_req + 2;
                        
                $headers_str = substr($header_buf, $start_headers, $end_headers - $start_headers);
                $this->headers = Basic::parse_headers($headers_str);
                
                $this->lc_headers = array();
                foreach ($this->headers as $k => $v){
                    $this->lc_headers[strtolower($k)] = $v;
                }                

                if (isset($this->lc_headers['transfer-encoding'])){
                    $this->is_chunked = $this->lc_headers['transfer-encoding'][0] == 'chunked';
                    
                    unset($this->lc_headers['transfer-encoding']);
                    unset($this->headers['Transfer-Encoding']);
                    
                    $this->content_len = 0;
                }else{                
                    $this->content_len = (int)@$this->lc_headers['content-length'][0];
                }
                
                $start_content = $end_headers + 4; // $end_headers is before last \r\n\r\n
                
                $data = substr($header_buf, $start_content);
                $header_buf = '';
                                
                $this->cur_state = static::READ_CONTENT;
                                
                // fallthrough to READ_CONTENT with leftover data
            case static::READ_CONTENT:
                
                if ($this->is_chunked){
                    $this->read_chunked_data($data);
                }else{
                    fwrite($this->content_stream, $data);
                    $this->content_len_read += strlen($data);
                    
                    if ($this->content_len - $this->content_len_read <= 0){
                        $this->cur_state = static::READ_COMPLETE;
                    }
                }                
                break;
            case static::READ_COMPLETE:
                break;
        }    
        
        if ($this->cur_state == static::READ_COMPLETE){
            fseek($this->content_stream, 0);
        }
    }
    
    function read_chunked_data($data){
        $content_stream =& $this->content_stream;
        $chunk_header_buf =& $this->chunk_header_buf;
        $chunk_len_remaining =& $this->chunk_len_remaining;
        $chunk_trailer_remaining =& $this->chunk_trailer_remaining;
        $chunk_state =& $this->chunk_state;
    
        while (isset($data[0])) { // keep processing chunks until we run out of data           
            switch ($chunk_state){           
                case static::READ_CHUNK_HEADER:
                    $chunk_header_buf .= $data;
                    $data = "";
                
                    $end_chunk_header = strpos($chunk_header_buf, "\r\n");
                    if ($end_chunk_header === false){// still need to read more chunk header
                        break; 
                    }
                    
                    // done with chunk header
                    $chunk_header = substr($chunk_header_buf, 0, $end_chunk_header);
                    
                    list($chunk_len_hex) = explode(";", $chunk_header, 2);
                                        
                    $chunk_len_remaining = intval($chunk_len_hex, 16);                    
                    
                    $chunk_state = static::READ_CHUNK_DATA;
                    
                    $data = substr($chunk_header_buf, $end_chunk_header + 2);
                    $chunk_header_buf = '';
                    
                    if ($chunk_len_remaining == 0){
                        $this->cur_state = static::READ_COMPLETE;
                        $this->headers['Content-Length'] = $this->lc_headers['content-length'] = array($this->content_len);
                        
                        // todo: this is where we should process trailers...
                        return;
                    }                    
                    
                    // fallthrough to READ_CHUNK_DATA with leftover data
                case static::READ_CHUNK_DATA:
                    if (strlen($data) > $chunk_len_remaining){
                        $chunk_data = substr($data, 0, $chunk_len_remaining);
                    }else{
                        $chunk_data = $data;
                    }
                    
                    $this->content_len += strlen($chunk_data);
                    fwrite($content_stream, $chunk_data);
                    $data = substr($data, $chunk_len_remaining);
                    $chunk_len_remaining -= strlen($chunk_data);
                    
                    if ($chunk_len_remaining == 0){          
                        $chunk_trailer_remaining = 2;
                        $chunk_state = static::READ_CHUNK_TRAILER;
                    }
                    break;
                case static::READ_CHUNK_TRAILER: // each chunk ends in \r\n, which we ignore
                    $len_to_read = min(strlen($data), $chunk_trailer_remaining);
                
                    $data = substr($data, $len_to_read);
                    $chunk_trailer_remaining -= $len_to_read;                        
                    
                    if ($chunk_trailer_remaining == 0){
                        $chunk_state = static::READ_CHUNK_HEADER;
                    }
                    break;
            }
        }
    }
    
    /* 
     * Returns the value of a HTTP header from this request (case-insensitive)
     */
    function get_header($name){
        return @$this->lc_headers[strtolower($name)][0];
    }
    
    /*
     * Returns true if a full HTTP request has been read by add_data().
     */
    function is_read_complete(){
        return $this->cur_state == static::READ_COMPLETE;
    }    
    
    /*
     * Sets a HTTPResponse object associated with this request
     */ 
    function set_response($response){
        $this->response = $response;
    }             
}
