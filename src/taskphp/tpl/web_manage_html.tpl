<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>hello taskPHP</title>
    <script type="text/javascript">function cmd_ajax() {
        var xmlhttp;
        if (window.XMLHttpRequest) {
          xmlhttp = new XMLHttpRequest();
        } else {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById("result").innerHTML = xmlhttp.responseText;
          }
        }
        var cmd_action_object = document.getElementById("cmd_action");
        var cmd_action_index = cmd_action_object.selectedIndex;
        var cmd_action_value = cmd_action_object.options[cmd_action_index].value;

        var cmd_content_object = document.getElementById("cmd_content");
        var cmd_content_index = cmd_content_object.selectedIndex;
        var cmd_content_value = cmd_content_object.options[cmd_content_index].value;

        var cmd_argv_object = document.getElementById("cmd_argv");
        var cmd_argv_value = cmd_argv_object.value;
        var url = document.domain + "/?action=" + cmd_action_value + "&content=" + cmd_content_value + "&argv=" + cmd_argv_value;
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
      }
      function task_ajax() {
        var xmlhttp;
        if (window.XMLHttpRequest) {
          xmlhttp = new XMLHttpRequest();
        } else {
          xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
          if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            document.getElementById("result").innerHTML = xmlhttp.responseText;
          }
        }
        var task_action_object = document.getElementById("task_action");
        var task_action_index = task_action_object.selectedIndex;
        var task_action_value = task_action_object.options[task_action_index].value;

        var task_content_object = document.getElementById("task_content");
        var task_content_index = task_content_object.selectedIndex;
        var task_content_value = task_content_object.options[task_content_index].value;

        var task_argv_object = document.getElementById("task_argv");
        var task_argv_value = task_argv_object.value;
        var url = document.domain + "/?action=" + task_action_value + "&content=" + task_content_value + "&argv=" + task_argv_value;
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
      }</script>
  </head>
  
  <body>
    <table border="0" width="98%" align="center" cellpadding="1" cellspacing="1" class="tbtitle" style="margin-left:1%;">
      <tbody>
        <tr>
          <td bgcolor="#F2F4F6">
            <strong>taskPHP远程管理器</strong></td>
        </tr>
        <form id="form1" name="form1" method="post" action=""></form>
        <tr align="center" bgcolor="#F2F4F6">
          <td align="left">
            <select id="cmd_action">
              <option value="cmd">cmd</option></select>
            <select id="cmd_content">
              <option value="restart">重启任务进程</option>
              <option value="close">关闭任务进程</option></select>参数:
            <input name="cmd_argv" type="text" id="cmd_argv" size="10" value="all" />
            <input type="button" onclick="cmd_ajax();" value="确定" /></td></tr>
        <form id="form2" name="form2" method="post" action=""></form>
        <tr align="center" bgcolor="#F2F4F6">
          <td align="left">
            <select id="task_action">
              <option value="task">task</option></select>
            <select id="task_content">
              <option value="select">查询任务</option>
              <option value="reload">重载任务</option>
              <option value="delete">删除任务</option></select>参数:
            <input name="task_argv" type="text" id="task_argv" size="10" value="all" />
            <input type="button" onclick="task_ajax();" value="确定" /></td></tr>
        <tr align="center" bgcolor="#FFFFFF">
          <td align="left">
            <textarea id="result" style="width:700px; height:400px">hello taskPHP</textarea></td>
        </tr>
      </tbody>
    </table>
  </body>
</html>