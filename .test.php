<?php
	class SteamLinuxTest extends PHPUnit_Framework_TestCase
	{
		public function testFileExists( )
		{
			// Trying to get dataProvider to work with depends in phpunit requires some serious magic
			$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'GAMES.json';
			
			$this->assertFileExists( $filePath );
			
			return $filePath;
		}
		
		/**
		 * @depends testFileExists
		 */
		public function testFileNotEmpty( $filePath )
		{
			$games = file_get_contents( $filePath );
			
			$this->assertNotEmpty( $games );
			
			return $games;
		}
		
		/**
		 * @depends testFileNotEmpty
		 */
		public function testJSON( $games )
		{
			$games = json_decode( $games, true );
			
			$this->assertTrue( json_last_error() === JSON_ERROR_NONE, 'JSON Error: ' . json_last_error_msg() );
			
			$allowedKeys = Array(
				'Working'    => 'is_bool',
				'Hidden'     => 'is_bool',
				'Beta'       => 'is_bool',
				'Comment'    => 'is_string',
				'CommentURL' => 'is_string'
			);
			
			foreach( $games as $appID => $keys )
			{
				$this->assertTrue( is_numeric( $appID ), 'Key "' . $appID . '" must be numeric' );
				$this->assertTrue( is_array( $keys ), 'Value of "' . $appID . '" must be an array' );
				
				foreach( $keys as $key => $value )
				{
					$this->assertArrayHasKey( $key, $allowedKeys, 'Invalid key "' . $key . '" in "' . $appID . '"' );
					$this->assertTrue( $allowedKeys[ $key ]( $value ), '"' . $key . '" in "' . $appID . '" is not "' . $allowedKeys[ $key ] . '"' );
					
					if( $key === 'CommentURL' )
					{
						$this->assertTrue( array_key_exists( 'Comment', $keys ), 'CommentURL key cant be without Comment key in "' . $appID . '"' );
					}
					else if( $key === 'Hidden' )
					{
						$this->assertFalse( array_key_exists( 'Working', $keys ), 'Working key cant be used along with Hidden key in "' . $appID . '"' );
						$this->assertFalse( array_key_exists( 'Beta', $keys ), 'Beta key cant be used along with Hidden key in "' . $appID . '"' );
						$this->assertTrue( array_key_exists( 'Comment', $keys ), 'Hidden app "' . $appID . '" must contain a Comment explaining why it was hidden' );
					}
				}
			}
			
			return $games;
		}
		
		/**
		 * @depends testJSON
		 */
		public function testSorting( $games )
		{
			$gamesOriginal = $games;
			
			ksort( $games );
			
			$this->assertTrue( $gamesOriginal === $games, 'File must be sorted correctly by appid' );
		}
	}
