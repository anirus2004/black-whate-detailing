<?php
// Этот файл подключается в index.php для отображения уведомлений
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php if (isset($_SESSION['success'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?php echo addslashes($_SESSION['success']); ?>', 'success');
});
</script>
<?php 
unset($_SESSION['success']); 
endif; 
?>

<?php if (isset($_SESSION['error'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    showAlert('<?php echo addslashes($_SESSION['error']); ?>', 'error');
});
</script>
<?php 
unset($_SESSION['error']); 
endif; 
?>