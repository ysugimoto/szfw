<?php

class RequestTest extends SZ_ClassTest
{
	public function setUp()
	{
		$this->class = new SZ_Request();
	}
	
	public function test_POSTのグローバル配列が取得できれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_getKey', $request);
		$this->assertInternalType('array', $ref->invoke($request, $GLOBALS, 'post'));
	}
	
	public function test_存在しないグローバル配列が取得できなければ通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_getKey', $request);
		$this->assertCount(0, $ref->invoke($request, $GLOBALS, '_foobar'));
	}
	
	public function test_Growメソッドが自クラスのインスタンスであれば通る()
	{
		$this->assertInstanceOf('SZ_Request', SZ_Request::grow());
	}
	
	public function test_serverメソッドの値が取得できれば通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('PHP_SELF' => 'index.php')));
		
		$this->assertEquals('index.php', $request->server('php_self'));
	}
	
	public function test_serverメソッドの値が取得できなければ通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('PHP_SELF' => 'index.php')));
		
		$this->assertFalse($request->server('undefined_index'));
	}
	
	public function test_postメソッドの値が取得できれば通る()
	{
		$request = new SZ_Request(array('_POST' => array('foo' => 'bar')));
		
		$this->assertEquals('bar', $request->post('foo'));
	}
	
	public function test_postメソッドの値が取得できなければ通る()
	{
		$request = new SZ_Request(array('_POST' => array('foo' => 'bar')));
		
		$this->assertFalse($request->post('undefined_index'));
	}
	
	public function test_getメソッドの値が取得できれば通る()
	{
		$request = new SZ_Request(array('_GET' => array('foo' => 'bar')));
		
		$this->assertEquals('bar', $request->get('foo'));
	}
	
	public function test_getメソッドの値が取得できなければ通る()
	{
		$request = new SZ_Request(array('_GET' => array('foo' => 'bar')));
		
		$this->assertFalse($request->get('undefined_index'));
	}
	
	public function test_cookieメソッドの値が取得できれば通る()
	{
		$request = new SZ_Request(array('_COOKIE' => array('foo' => 'bar')));
		
		$this->assertEquals('bar', $request->cookie('foo'));
	}
	
	public function test_cookieメソッドの値が取得できなければ通る()
	{
		$request = new SZ_Request(array('_COOKIE' => array('foo' => 'bar')));
		
		$this->assertFalse($request->cookie('undefined_index'));
	}
	
	public function test_pathInfoが正規化されて戻れば通る()
	{
		$request = new SZ_Request();
		$this->assertEquals('foo/bar',$request->setRequest('/foo/bar', SZ_MODE_MVC, 1));
	}
	
	public function test_pathinfoが渡されない場合は環境変数が戻れば通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('PATH_INFO' => '/foo/bar')));
		$this->assertEquals('foo/bar', $request->setRequest('', SZ_MODE_MVC, 1));
	}
	
	public function test_セグメントデータが取得できれば通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/index', SZ_MODE_MVC, 0);
		
		$this->assertEquals('welcome', $request->segment(1));
	}

	public function test_セグメントデータが取得できない場合に通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/index', SZ_MODE_MVC, 0);
		
		$this->assertFalse($request->segment(5));
	}
	
	public function test_セグメントデータが取得できない場合にデフォルト値が戻れば通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/index', SZ_MODE_MVC, 1);
		
		$this->assertEquals('default', $request->segment(5, 'default'));
	}
	
	public function test_セグメント配列が取得できれば通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/sample/index', SZ_MODE_MVC, 1);
		
		$this->assertInternalType('array', $request->uriSegments(1));
	}
	
	public function test_セグメント配列の数が一致していれば通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/sample/index', SZ_MODE_MVC, 1);
		
		$this->assertCount(3, $request->uriSegments(1));
	}
	
	public function test_セグメント配列の中身が全て文字列であれば通る()
	{
		$request = new SZ_Request();
		$request->setRequest('/welcome/sample/index', SZ_MODE_MVC, 1);
		
		$this->assertContainsOnly('string', $request->uriSegments(1));
	}
	
	public function test_PATH_INFOがアクセスしたものと一致していれば通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('PATH_INFO' => '/foo/bar')));
		$this->assertEquals('/foo/bar', $request->getAccessPathInfo());
	}
	
	public function test_IPアドレスが取得できない場合のデフォルト値が戻れば通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('PATH_INFO' => '/foo/bar')));
		$this->assertEquals('0.0.0.0', $request->ipAddress());
	}
	
	public function test_IPアドレスが取得が取得できた場合に通る()
	{
		$request = new SZ_Request(array('_SERVER' => array('REMOTE_ADDR' => '48.212.153.45')));
		$this->assertEquals('48.212.153.45', $request->ipAddress());
	}
	
	public function test_通常文字のフィルタリングが成功していれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_filterString', $request);
		
		$this->assertEquals('test', $ref->invoke($request, 'test', 'UTF-8'));
	}
	
	public function test_改行文字がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_filterString', $request);
		
		$this->assertEquals("abcdefg\n", $ref->invoke($this->class, "abcdefg\r\n", 'UTF-8'));
	}

	public function test_不可視文字がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_filterString', $request);
		
		$this->assertEquals("", $ref->invoke($request, "\x00\x01\x02\x04\x05\x06\x07\x08\x0C\x0B\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F", 'UTF-8'));
	}
	
	public function test_SJISエンコードがフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_filterString', $request);
		
		$this->assertEquals('test', $ref->invoke($request, mb_convert_encoding('test', 'SHIFT_JIS'), 'SHIFT_JIS'));
	}
	
	public function test_EUCエンコードがフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_filterString', $request);
		
		$this->assertEquals('test', $ref->invoke($request, mb_convert_encoding('test', 'EUC-JP'), 'EUC-JP'));
	}
	
	public function test_通常文字の配列がフィルタリングが成功していれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_cleanFilter', $request);
		
		$this->assertEquals(array('key' => 'test'), $ref->invoke($request, array('key' => 'test'), 'UTF-8'));
	}
	
	public function test_改行文字の配列がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_cleanFilter', $request);
		
		$this->assertEquals(array('key' => "abcdefg\n"), $ref->invoke($request, array('key' => "abcdefg\r\n"), 'UTF-8'));
	}

	public function test_不可視文字の配列がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_cleanFilter', $request);
		
		$this->assertEquals(array('key' => ""), $ref->invoke($request, array('key' => "\x00\x01\x02\x04\x05\x06\x07\x08\x0C\x0B\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F"), 'UTF-8'));
	}
	
	public function test_SJISエンコード配列がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_cleanFilter', $request);
		
		$this->assertEquals(array('key' => 'test'), $ref->invoke($request, array(mb_convert_encoding('key', 'SHIFT_JIS') => mb_convert_encoding('test', 'SHIFT_JIS')), 'SHIFT_JIS'));
	}
	
	public function test_EUCエンコード配列がフィルタリングされていれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_cleanFilter', $request);
		
		$this->assertEquals(array('key' => 'test'), $ref->invoke($request, array(mb_convert_encoding('key', 'EUC-JP') => mb_convert_encoding('test', 'EUC-JP')), 'EUC-JP'));
	}
	
	
	public function test_SJIS_UTF8にエンコードされれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_convertUTF8', $request);
		
		// encoding-shift_jis
		$this->assertEquals('test', $ref->invoke($request, mb_convert_encoding('test', 'SHIFT_JIS')));
	}
	
	public function test_EUC_UTF8にエンコードされれば通る()
	{
		$request = new SZ_Request();
		$ref = $this->setMethodAccessible('_convertUTF8', $request);
		
		// encoding-shift_jis
		$this->assertEquals('test', $ref->invoke($request, mb_convert_encoding('test', 'EUC-JP')));
	}
	

}
