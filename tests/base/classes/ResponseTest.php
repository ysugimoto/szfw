<?php

class ResponseTest extends SZ_ClassTest
{
	public function setUp()
	{
		$this->class = new SZ_Response();
	}
	
	public function test_Requestインスタンスが戻れば通る()
	{
		$this->assertInstanceOf('SZ_Response', SZ_Response::grow());
	}
	
	public function test_redirectメソッドの戻り値が自身のインスタンスであれば通る()
	{
		$response = new SZ_Response();
		
		$this->assertSame($response, $response->redirect('/'));
	}
	
	public function test_redirectヘッダがスタックに積まれていれば通る()
	{
		$response = new SZ_Response();
		$response->redirect('/');
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$this->assertRegExp('/^Location:\s/', $headers[0][0]);
	}
	
	public function test_httpから始まるリダイレクト指定のヘッダ指定がスタックに積まれていれば通る()
	{
		$response = new SZ_Response();
		$response->redirect('http://google/com');
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$this->assertRegExp('/^Location:\shttp:\/\//', $headers[0][0]);
	}
	
	public function test_setHeaderメソッド後にスタックに積まれていれば通る()
	{
		$response = new SZ_Response();
		$response->setHeader('Content-Type', 'text/plain');
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$this->assertContains('Content-Type: text/plain', $headers[0]);
	}
	
	public function noCacheメソッド後にスタックに積まれていれば通る()
	{
		$response = new SZ_Response();
		$response->noCache();
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$this->assertCount(3, $headers);
	}
	
	public function test_setBodyメソッドからの出力が自身のインスタンスであれば通る()
	{
		$response = new SZ_Response();
		$this->assertSame($response, $response->setBody('test_output'));
	}
	
	public function test_setBodyメソッド後に出力キューに入れば通る()
	{
		$response = new SZ_Response();
		$response->setBody('test_output');
		$output = $this->getProtectedProperty('outputQueue', $response);
		
		$this->assertEquals('test_output', $output);
	}
	
	/*
	public function test_JSONメソッドの引数が文字列の場合はそのまま出力されれば通る()
	{
		$test = json_encode(array('test' => 'data'));
		$response = new SZ_Response();
		
		$this->assertSame($test, $response->displayJSON($test));
	}
	
	public function test_JSONメソッドの引数が文字列以外の場合はエンコードされて出力されれば通る()
	{
		$test = array('test' => 'data');
		$response = new SZ_Response();
		
		$this->assertSame(json_encode($test), $response->displayJSON($test));
	}
	
	public function test_JSONメソッド実行時に適切なヘッダが設定されていれば通る()
	{
		$test = json_encode(array('test' => 'data'));
		$response = new SZ_Response();
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$this->assertContains('Content-Type: application/json', $headers[1]);
	}
	*/
	
	public function test_ファイルダウンロード時に適切なヘッダが指定されていれば通る()
	{
		$response = new SZ_Response();
		$response->download(SZPATH . 'tests/base/phpunit.xml');
		$headers = $this->getProtectedProperty('_headers', $response);
		
		$header = array_shift($headers);
		$this->assertEquals('Content-Disposition: attachment; filename="phpunit.xml"', $header[0]);
		
		$header = array_shift($headers);
		$this->assertEquals('Content-Transfar-Encoding: binary', $header[0]);
		
		$header = array_shift($headers);
		$this->assertEquals('Expires: 0', $header[0]);
		
		$header = array_shift($headers);
		$this->assertEquals('Pragma: no-cache', $header[0]);
		
		$header = array_shift($headers);
		$this->assertEquals('Content-Length: ' . filesize(SZPATH . 'tests/base/phpunit.xml'), $header[0]);
	}
}
