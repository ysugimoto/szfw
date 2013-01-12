<?php

class RouterTest extends SZ_ClassTest
{
	public function setUp()
	{
		$this->class = new SZ_Router();
	}
	
	public function test_pathinfoが正しくセットされていればパス()
	{
		$this->class->setPathInfo('/welcome/index');
		$this->assertEquals('/welcome/index', $this->class->getInfo('pathinfo'));
	}
	
	public function test_CLIモードが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_CLI);
		$this->assertEquals(SZ_MODE_CLI, $this->class->getInfo('mode'));
	}
	
	public function test_ACTIONモードが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_ACTION);
		$this->assertEquals(SZ_MODE_ACTION, $this->class->getInfo('mode'));
	}
	
	public function test_PROCモードが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_PROC);
		$this->assertEquals(SZ_MODE_PROC, $this->class->getInfo('mode'));
	}
	
	public function test_MVCモードが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_MVC);
		$this->assertEquals(SZ_MODE_MVC, $this->class->getInfo('mode'));
	}
	
	public function test_CLIモードのコントローラ検索パスが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_CLI);
		
		$this->assertEquals('classes/cli/', $this->getProtectedProperty('detectDir'));
	}
	
	public function test_ACTIONモードのコントローラ検索パスが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_ACTION);
		
		$this->assertEquals('scripts/actions/', $this->getProtectedProperty('detectDir'));
	}
	
	public function test_PROCモードのコントローラ検索パスが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_PROC);
		
		$this->assertEquals('scripts/processes/', $this->getProtectedProperty('detectDir'));
	}
	
	public function test_MVCモードのコントローラ検索パスが正しくセットされていれば通る()
	{
		$this->class->setMode(SZ_MODE_MVC);
		
		$this->assertEquals('classes/controllers/', $this->getProtectedProperty('detectDir'));
	}
	
	
	public function test_プロセスレベルが正しくセットされていれば通る()
	{
		$this->class->setLevel(1);
		$this->assertEquals(1, $this->class->getInfo('level'));
	}
	
	public function test_ルーティングが成功すれば通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_MVC);
		$router->setLevel(1);
		$router->setPathInfo('welcome/index');
		
		$ref = $this->setMethodAccessible('routing', $router);
		$this->assertTrue($ref->invoke($router));
	}
	
	public function test_getInfoでパラメータが取得できれば通る()
	{
		$this->class->setMode(SZ_MODE_MVC);
		$this->assertNotEmpty($this->class->getInfo('mode'));
	}
	
	public function test_getInfoで存在しないパラメータは空文字が戻れば通る()
	{
		$this->assertEmpty($this->class->getInfo('prop'));
	}
	
	public function test_MVCコントローラが検索できれば通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_MVC);
		$ref = $this->setMethodAccessible('_detectController', $router);
		
		$this->assertInternalType('array', $ref->invoke($this->class,
		                                                array('welcome', 'index'),
		                                                Application::get()->path . 'classes/controllers/'
		                                                )
		                         );
	}
	
	public function test_MVCコントローラが検索できなければ通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_MVC);
		$router->setPathInfo('foo/bar');
		$ref = $this->setMethodAccessible('_detectController', $router);
		
		$this->assertFalse($ref->invoke($router,
		                                array('welcometest', 'sample'),
		                                Application::get()->path . 'classes/controllers/'
		                               )
		                         );
	}
	
	public function test_ACTIONモードで起動できなければ通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_ACTION);
		$router->setPathInfo('foo/bar');
		
		$this->assertFalse($router->bootAction());
	}
	
	public function test_PROCモードで起動できなければ通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_ACTION);
		$router->setPathInfo('foo/bar');
		
		$this->assertFalse($router->bootProcess());
	}
	
	public function test_MVCモードで起動できれば通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_MVC);
		
		$this->assertInternalType('array', $router->bootController());
	}

	public function test_MVCモードでの起動結果が配列の最初がコントローラインスタンスであれば通る()
	{
		$router = new SZ_Router();
		$router->setMode(SZ_MODE_MVC);
		
		list($Controller, $result) = $router->bootController();
		$this->assertInstanceOf('SZ_Breeder', $Controller);
	}
}
