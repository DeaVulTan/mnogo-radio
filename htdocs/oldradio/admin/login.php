<?
session_start();

if ( $_POST['password'] != '' ) 
{
    $_SESSION['password'] = $_POST['password'];
    header('Location: /admin/');
}
?>

<br><br><br><br>
<center>
<form method="POST" action="login.php">
<table style="border:1px dotted black;" cellpadding="3">
<tr>
    <td>Пароль:</td>
    <td><input type="password" name="password"></td>
</tr>
<tr>
    <td></td>
    <td><input type="submit" value="Войти?"></td>
</tr>
</table>
</form>
<?
if ( isset($_SESSION["error"]) ) 
{
    echo "<font color='red'>{$_SESSION['error']}</font>";
    unset($_SESSION['error']);
}
?>
</center>
