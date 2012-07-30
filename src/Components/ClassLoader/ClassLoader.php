<?php
/**
 * Class AutoLoader for PHP 5.2/5.3
 * Partially implements standard of
 * http://groups.google.com/group/php-standards/web/psr-0-final-proposal?pli=1
 * Author    : chorry@rbc.ru
 * Created at: 11.08.11 16:37
 */
class ClassLoader
{

	private $_fileExtension = '.php';
	private $_includePath = '../';
	private $_prefixes = array();
	private $_namespace; //we can't use 5.3 :`(
	private $_namespaceSeparator = '\\';

	public function __construct()
	{
		$this->_prefixes[ ] = '/';
		$this->_prefixes[ ] = '../';
		$this->_prefixes[ ] = '../Classes/';
		$this->_prefixes[ ] = '../Components';
	}

	/**
	 * Регистрирует автозагрузчик
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register( array( $this, 'loadClass' ) );
	}

	/***
	 * Регистрирует дополнительные пути для поиска автозагрузчика
	 * @param $prefix
	 */
	public function registerPrefix( $prefix )
	{
		$this->_prefixes[ ] = $prefix;
	}

	/**
	 * Загружает указанный класс
	 */
	public function loadClass( $className )
	{
		if ( $file = $this->findFile( $className ) )
		{
			require $file;
		}
		else
		{
			//не нашли класс, вот блин
			//throw new Exception ("Failed to load class ".$className."\n");
		}
	}

	/**
	 * Пытается найти файл класса по ожидаемому адресу
	 *  old-school style:
	 * Components_ClassLoader_ClassLoader => Components/ClassLoader/ClassLoader.php
	 * @param $className
	 * @return $fileName
	 */
	public function findFile( $className )
	{
		$className = $this->parseNamespace( $className );
		$fileName  = '';
		$fileName .= str_replace( '_', DIRECTORY_SEPARATOR, $className ) . $this->_fileExtension;
		$search[ ] = $this->_includePath . $fileName;
		if ( file_exists( $this->_includePath . $fileName ) )
		{
			return $this->_includePath . $fileName;
		}

		foreach ( $this->_prefixes as $prefix )
		{
			$fpath     = __DIR__ . '/' . $this->_includePath . $prefix . $fileName;
			$search[ ] = $fpath;
			if ( file_exists( $fpath ) )
			{
				return $fpath;
			}
			else
			{
			}
		}
		return false;
	}


	public function parseNamespace( $namespaceClass )
	{
		return str_replace( '\\', '/', $namespaceClass );
	}
}
