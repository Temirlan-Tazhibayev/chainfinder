<?php
define('WWW_PATH', str_replace('\\', '/', __DIR__));
require("config.php");
require("ErrorHandler.php");

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  throw new ErrorException($message, 0, $severity, $filename, $lineno);
}
set_error_handler('exceptions_error_handler');
spl_autoload_register(function ($class_name) {
  if (strpos($class_name, 'Action') !== false) {
      $iPos = strrpos($class_name, '\\') + 1;
      $class_name = substr($class_name, 0, $iPos) . 'schema\\controller\\' . substr($class_name, $iPos);
  }

  if (file_exists($class_name . '.php')) {
      // echo 'Autoloading: ' . $class_name . '<br>';
      include $class_name . '.php';
  } else {
      echo 'some error';
  }
});

new schema\model\BootStrap($aConfig);
$conn = new schema\model\Database($aConfig);
?>
</html>
