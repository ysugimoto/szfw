<?php

class ViewTest extends SZ_ClassTest
{
	public function setUp()
	{
		$this->class = new SZ_View();
	}
	
	public function test_growの結果が自身を継承しているクラスインスタンスであれば通る()
	{
		$this->assertInstanceOf('SZ_View', SZ_View::grow());
	}
	
	public function test_assignした変数が含まれていれば通る()
	{
		$this->class->assign('test', 'data');
		$vars = $this->getProtectedProperty('_assignedVars');
		
		$this->assertContains('data', $vars);
	}
	
	public function test_assignしたデータ名がキーとして存在していれば通る()
	{
		$this->class->assign('test', 'data');
		$vars = $this->getProtectedProperty('_assignedVars');
		
		$this->assertArrayHasKey('test', $vars);
	}
	
	public function test_addメソッドキューデータが追加されていれば通る()
	{
		$this->class->add('testdata');
		
		$this->assertSame('testdata', $this->class->getDisplayBuffer());
	}
	
	public function test_bufferStartで出力バッファのカウントがアップすれば通る()
	{
		$this->class->bufferStart();
		
		$this->assertEquals(2, ob_get_level());
		
		$this->class->getBufferEnd();
	}
	
	public function test_getBufferEndメソッドで出力バッファを終了した時出力キューデータに追加されていれば通る()
	{
		$this->class->bufferStart();
		echo 'testdata';
		
		$this->class->getBufferEnd();
		$this->assertSame('testdata', $this->class->getDisplayBuffer());
	}
	
	public function test_getBufferEndメソッドで出力バッファを終了した時バッファデータがそのまま戻れば通る()
	{
		$this->class->bufferStart();
		echo 'testdata';
		
		$this->assertSame('testdata', $this->class->getBufferEnd(TRUE));
	}
	
	public function test_renderメソッドの第三引数がある場合はそのまま出力が戻れば通る()
	{
		$this->assertNotNull($this->class->render('welcome/index', array(), TRUE));
	}
	
	public function test_renderメソッドの第三引数が無い場合は戻り値がなければ通る()
	{
		$this->assertNull($this->class->render('welcome/index'));
	}
	
	public function test_escapeRenderメソッドの第三引数がある場合はそのまま出力が戻れば通る()
	{
		$this->assertNotNull($this->class->escapeRender('welcome/index', array(), TRUE));
	}
	
	public function test_escapeRenderメソッドの第三引数が無い場合は戻り値がなければ通る()
	{
		$this->assertNull($this->class->escapeRender('welcome/index'));
	}
	
	public function test__renderViewメソッドの第三引数で返却指定がある場合はそのまま出力が戻れば通る()
	{
		$ref  = $this->setMethodAccessible('_renderView');
		
		$this->assertNotNull($ref->invoke($this->class, 'welcome/index', array(), TRUE));
	}
	
	public function test__renderViewメソッドの第三引数で返却指定が無い場合は戻り値がなければ通る()
	{
		$ref  = $this->setMethodAccessible('_renderView');
		
		$this->assertNull($ref->invoke($this->class, 'welcome/index', array(), FALSE));
	}
	
	public function test_setメソッドで最終レンダリングファイルがスイッチされていれば通る()
	{
		$this->class->set('welcome/index');
		
		$this->assertSame('welcome/index', $this->getProtectedProperty('_finalView'));
	}
	
	public function test_layoutメソッドでレイアウト指定がセットされれば通る()
	{
		$this->class->layout(); // default
		
		$this->assertSame('default', $this->getProtectedProperty('_layout'));
	}
	
	public function test_addPartsでテンプレートパーツが追加されれば通る()
	{
		$this->class->addParts('test', 'welcome/index', array());
		
		$ary = $this->getProtectedProperty('_layoutParts');
		$this->assertArrayHasKey('test', $ary);
	}
	
	/*
	 * @expectedException RuntimeException
	 */
	public function test_レイアウトが存在しなければ例外が投げられる()
	{
		try
		{
			// Expected Exception
			$this->class->displayLayout();
		}
		catch ( RuntimeException $e )
		{
			return;
		}
		
		$this->fail('Failed Exception testing...');
	}
	
	public function test_defaultエンジンに変更されれば通る()
	{
		$this->class->engine();
		
		$this->assertInstanceOf('SZ_Default_view', $this->getProtectedProperty('driver'));
	}
	
	public function test_smartyエンジンに変更されれば通る()
	{
		try
		{
			$this->class->engine('smarty');
		}
		catch ( Exception $e )
		{
			//$this->markTestSkipped('Smary is not insalled. skip this test.');
			return;
		}
		$this->assertInstanceOf('SZ_Smarty_view', $this->getProtectedProperty('driver'));
		
	}
	
	public function test_phptalエンジン名に変更されれば通る()
	{
		try
		{
			$this->class->engine('phptal');
		}
		catch ( Exception $e )
		{
			//$this->markTestSkipped('PHPTAL is not insalled. skip this test.');
			return;
		}
		$this->assertInstanceOf('SZ_Phptal_view', $this->getProtectedProperty('driver'));
	}

	public function test_エンジン変更でドライバが変更されれば通る()
	{
		$this->class->engine('default');
		
		$this->assertInstanceOf('SZ_View_Driver', $this->getProtectedProperty('driver'));
	}
	
	public function test_getEngineメソッドでエンジン名が取得できれば通る()
	{
		$this->class = new SZ_View();
		
		$this->assertSame('default', $this->getProtectedProperty('_templateEngine'));
	}
	
	public function test_setExtentionメソッドで拡張子が変更されれば通る()
	{
		$this->class->setExtension('tpl');
		
		$this->assertSame('.tpl', $this->getProtectedProperty('_templateExtension'));
	}
	
	public function test_getExtensionメソッドで拡張子が取得できれば通る()
	{
		$this->class->setExtension('tpl');
		
		$this->assertSame('.tpl', $this->class->getExtension());
	}
	
}
