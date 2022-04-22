<!DOCTYPE html>
<html>
<head>
  
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>

<h2>Error List (<?php echo $acc_name;?>)<?php date_default_timezone_set("Asia/Kuala_Lumpur"); echo ' '.date("Y-m-d H:i:s");?></h2>

<table>
  <tr>
    <th>COUNT</th>
    <th>STATUS</th>
  </tr>
  <?php
    foreach($list as $row)
    {
      ;?>
      <tr><td><?php echo $row->count;?></td><td><?php echo $row->message;?></td></tr>
      <?php
    }
  ;?>
</table>

</body>
</html>
