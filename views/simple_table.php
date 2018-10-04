<table style="border:1px solid black;">
<tr>
	<th>id</th>
	<th>name</th>
	<th>city</th>
</tr>
<?php
	foreach ($this->_response as $id => $userData) {
?>
	<tr style="background-color:lightgreen">
		<td><?=$id; ?></td>
		<td><?=$userData['name']; ?></td>
		<td><?=$userData['city']; ?></td>
	</tr>
<?php	
	}
?>
</table>
<a href="http://<?php echo $_SERVER['HTTP_HOST'] . '/' . ENTRY_POINT_NAME ?>">Back to entry point</a>