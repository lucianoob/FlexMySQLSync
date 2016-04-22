<?php

class PHPBDService {
	public function PHPBDService() {
		
	}
	private function openConexao($usuario,$senha,$servidor,$porta) {
		$conexao = new mysqli($servidor, $usuario, $senha);
		if (!$conexao) {
			return false;
		} else {
			return $conexao;
		}
	}
	public function testarConexao($usuario,$senha,$servidor,$porta) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao) {
			return false;
		} else {
			return true;
		}
	}
	public function listarBancoDados($usuario,$senha,$servidor,$porta) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		$query = "show databases";
		$result = $conexao->query($query);
		$rows = array();
		$i = 0;
		while($row = $result->fetch_assoc()) {
			$rows[$i] = $row;
			$i++;
		}
		$this->closeConexao($conexao);
		return $rows;
	}
	public function listarTabelas($usuario,$senha,$servidor,$porta,$bancoDados) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao->select_db($bancoDados)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$query = "show tables";
		$result = $conexao->query($query);
		$rows = array();
		$i = 0;
		while($row = $result->fetch_assoc()) {
			$rows[$i] = $row;
			$i++;
		}
		$this->closeConexao($conexao);
		return $rows;
	}
	public function listarCampos($usuario,$senha,$servidor,$porta,$bancoDados,$tabela) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao->select_db($bancoDados)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$query = "SHOW FULL COLUMNS FROM ".$tabela;
		$result = $conexao->query($query);
		$rows = array();
		$i = 0;
		while($row = $result->fetch_assoc()) {
			$rows[$i] = $row;
			$i++;
		}
		$this->closeConexao($conexao);
		return $rows;
	}
	public function compararTodasTabelas($usuario,$senha,$servidor,$porta,$bancoDados,$usuario1,$senha1,$servidor1,$porta1,$bancoDados1,$iscomment,$iserror) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao->select_db($bancoDados)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$query0 = "show tables";
		$result0 = $conexao->query($query0);
		$conexao1 = $this->openConexao($usuario1,$senha1,$servidor1,$porta1);
		if (!$conexao1->select_db($bancoDados1)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$return = "";
		$cont = 0;
		$sql = "";
		while($row = $result0->fetch_array()) {
			//print_r($row);
			//exit();
			
			$query = "SHOW FULL COLUMNS FROM ".$row[0];
			$result = $conexao->query($query);
			$query1 = "SHOW FULL COLUMNS FROM ".$row[0];
			if($result1 = $conexao1->query($query1)) {
				$return .= "\n\n------------------------------------------------------------------------------------";
				$return .= "\nTabela: '$row[0]'";
				$sqlAlter = "ALTER TABLE $row[0] ";
				$contAlter = 0;
				$fieldOld = "";
				while($campo = $result->fetch_object()) {
					$erroString = "";
					$comment =  "";
					$contErros = 0;
					$campo1 = $result1->fetch_object();
					//print_r($campo1);
					if($campo1 != null) {
						$queryD = "SHOW FULL COLUMNS FROM ".$row[0]." LIKE '".$campo1->Field."'";
						//echo $queryD;
						//exit();
						$resultD = $conexao->query($queryD);
						if(!$resultD->fetch_object()) {
							$cont++;
							$sqlAlter .= "\n\tDROP `$campo1->Field`,";
						} else {
							if($campo->Field != $campo1->Field) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O nome '$campo->Field' é diferente do nome '$campo1->Field'.";
								$cont++;
								$contErros++;
							}
							if($campo->Type != $campo1->Type) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O tipo '$campo->Type' é diferente do tipo '$campo1->Type'.";
								$cont++;
								$contErros++;
							}
							if($campo->Collation != $campo1->Collation) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O codificação '$campo->Collation' é diferente do codificação '$campo1->Collation'.";
								$cont++;
								$contErros++;
							}
							if($campo->Null != $campo1->Null) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O nulo '$campo->Null' é diferente do nulo '$campo1->Null'.";
								$cont++;
								$contErros++;
							}
							if($campo->Key != $campo1->Key) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> A chave '$campo->Key' é diferente da chave '$campo1->Key'.";
								$cont++;
								$contErros++;
							}
							if($campo->Default != $campo1->Default) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O padrão '$campo->Default' é diferente do padrão '$campo1->Default'.";
								$cont++;
								$contErros++;
							}
							if($campo->Extra != $campo1->Extra) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O extra '$campo->Extra' é diferente do extra '$campo1->Extra'.";
								$cont++;
								$contErros++;
							}
							if($campo->Privileges != $campo1->Privileges) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> Os privilégios '$campo->Privileges' é diferente dos privilégios '$campo1->Privileges'.";
								$cont++;
								$contErros++;
							}
							if($campo->Comment != $campo1->Comment && $iscomment) {
								$erroString .= "\n\t\t<font color='#FF0000'>ERRO:</font> O comentário '$campo->Comment' é diferente do comentário '$campo1->Comment'.";
								$cont++;
								$contErros++;
								$comment =  "COMMENT '$campo->Comment'";
							}
							if(!$iserror || $erroString != "")
								$return .= "\n\tCampo: '$campo->Field'".$erroString;
							if(!$contErros && !$iserror)
								$return .= "\t<font color='#0000FF'>[OK]</font>";
							if($contErros) {
								if($contAlter)
									$sqlAlter .= ",";
								$contAlter++;
								if($campo->Collation != "")
									$campo->Collation = "COLLATE $campo->Collation";
								if($campo->Null == "NO")
									$campo->Null = "NOT NULL";
								$sqlAlter .= "\n\tCHANGE $campo->Field $campo->Field $campo->Type $campo->Collation $campo->Null $campo->Extra $comment";
								$queryE = "SHOW FULL COLUMNS FROM ".$row[0]." LIKE '$campo->Field'";
								$resultE = $conexao1->query($queryE);
								if(!$resultE->fetch_object())
									$sqlAlter = str_replace("CHANGE ".$campo->Field, "ADD", $sqlAlter)."AFTER $fieldOld";
							}		
							$fieldOld = $campo->Field;
						}
					}
				}
				//ALTER TABLE  `erpgrupos` ADD  `teste` INT( 12 ) NOT NULL AFTER  `GRPdInclusao`
				$sqlAlter .= ";\n";
				if($sqlAlter != "ALTER TABLE $row[0] ;\n")
					$sql .= $sqlAlter; 
				$return .= "\n------------------------------------------------------------------------------------";
			} else {
				$return .= "\n\n<font color='#FF0000'>ERRO:</font> a tabela '$row[0]' não existe no banco de dados '$bancoDados1' !!!";
				$cont++;
				$sql .= "\n\nDROP TABLE IF EXISTS `$row[0]`;";
				$sql .= "\nCREATE TABLE IF NOT EXISTS `$row[0]` (\n";
				$keys = "";
				while($campo = $result->fetch_object()) {
					if($campo->Null == "NO")
						$campo->Null = "NOT NULL";
					if($campo->Extra != "")
						$campo->Extra = " ".$campo->Extra;
					if($campo->Collation != "")
						$campo->Collation = "COLLATE $campo->Collation ";
					if($campo->Comment != "")
						$campo->Comment =  " COMMENT '$campo->Comment'";
					$sql .= "\t`$campo->Field` $campo->Type $campo->Collation$campo->Null$campo->Extra$campo->Comment,\n";
					if($campo->Key == "PRI")
						$keys = $campo->Field;
				}
				if($keys != "")
					$sql .= "\tPRIMARY KEY (`$keys`)\n";
				$sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			}
		}
		$this->closeConexao($conexao);
		$this->closeConexao($conexao1);
		$returns = array();
		$returns[0] = $return;
		$returns[1] = $cont;
		$returns[2] = $sql;
		return $returns;
	}
	public function listarDados($usuario,$senha,$servidor,$porta,$bancoDados,$tabela) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao->select_db($bancoDados)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$query = "SELECT * FROM ".$tabela;
		$result = $conexao->query($query);
		$rows = array();
		$i = 0;
		while($row = $result->fetch_assoc()) {
			$rows[$i] = $row;
			$i++;
		}
		$this->closeConexao($conexao);
		return $rows;
	}
	public function executarSQL($usuario,$senha,$servidor,$porta,$bancoDados,$sql) {
		$conexao = $this->openConexao($usuario,$senha,$servidor,$porta);
		if (!$conexao->select_db($bancoDados)) {
			die ('Não foi possível conectar com o banco de dados: ' . mysql_error());
		}
		$querys = explode(";", $sql);
		for($i=0; $i<count($querys); $i++) {
			$result = $conexao->query($querys[$i]);
		}
		$this->closeConexao($conexao);
		return true;
	}
	private function closeConexao($conexao) {
		if(!$conexao->close()) {
			die ('Não foi possível fechar a conexão com o banco de dados: ' . mysql_error());
		}
		return true;
	}
}

//$t = new PHPBDService();
//echo("<pre>")
//echo($t->testarConexao("root","ia010458","localhost","3306"));
//echo("</pre>");
?>
