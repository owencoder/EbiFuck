	<?php
		class Ebifuck
		{
			private $defineEbi;
			private $ptr = 0;
			private $pc = 0;
			private $buffer;
			private $buffer_size;
			private $memory;
			private  $nest = 0;

			//	コンストラクタで定義すっか
			function __construct()
			{
				$this->defineEbi = array (
					//	ポインタをインクリメント
					"エ" => function($o) { $o->ptr ++; return null; },
					//	ポインタをデクリメント
					"ビ" => function($o) { $o->ptr --; return null; },
					//	ポインタが指す値をインクリメント
					"フ" => function($o) { $o->memory[$o->ptr] ++; return null; },
					//	ポインタが指す値をデクリメント
					"ラ" => function($o) { $o->memory[$o->ptr] --; return null; },
					//	ポインタが指す値を出力に書き出す
					"イ" => function($o) { return $o->memory[$o->ptr]; },
					//	ポインタが指す値が0なら、対応する]の直後にジャンプする
					'[' => function($o)
					{
						//	値は0だよな？
						if ( !$o->memory[$o->ptr] )
						{
							for ( ;; )
							{
								//	プログラムカウンタを増加
								$c = mb_substr($o->buffer, ++$o->pc, 1, "utf-8");
								if ( $c === '[' )
								{
									$this->nest ++;
									break;
								}
								if ( $c === ']' )
								{
									$this->nest --;
									break;
								}
							}
						}
						return null;
					},
					//	ポインタが指す値が0でないなら、対応する[の直後にジャンプする
					']' => function($o)
					{
						for ( ;; )
						{
							//	プログラムカウンタを減らす
							$c = mb_substr($o->buffer, --$o->pc, 1, "utf-8");
							if ( $c === '[' )
							{
								$this->nest --;
								break;
							}
							if ( $c === ']' )
							{
								$this->nest ++;
								break;
							}
						}
						//	消す
						$o->pc --;
						return null;
					}
				);
			}

			//	実行命令
			function exec ( $code )
			{
				$this->buffer = $code;
				$this->memory = array_fill(0, 1024*1024, 0);
				$this->buffer_size = mb_strlen($this->buffer, "utf-8");
				$result = "";

				for( $this->pc = 0, $this->ptr = 0; $this->pc < $this->buffer_size; $this->pc ++)
				{
					//	命令あるか？
					$c = mb_substr($this->buffer, $this->pc, 1, "utf-8");
					if ( !array_key_exists($c, $this->defineEbi) )
						{ continue; }

					//	あったら実行
					$w = $this->defineEbi[$c]($this);

					//	ついでに出力も拾う
					if ( !is_null ( $w ) )
					{
						$result = sprintf ( "%s%c", $result, $w );
					}
				}
				return $result;
			}
		}

		//	ためし
		$d = new Ebifuck ();
		echo $d->exec ( "フフフフフフフフフ[エフフフフフフフフエフフフフフフフフフフフエフフフフフビビビラ]エイエフフイフフフフフフフイイフフフイエライ
ラララララララララララライビフフフフフフフフイラララララララライフフフイラララララライラララララララライエフイ" );