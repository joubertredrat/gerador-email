<?php
/**
 * Objeto gerador de e-mails randômicos, sua utilização é muito facil, veja um exemplo abaixo:
 *
 *	GeradorEmail::iniciar()
 *		->setQuantidade(10)
 *		->setQueryContinua(false)
 *		->setSomenteBr(true)
 *		->setQuery('INSERT INTO contato_dadosweb (nome, email, ip, data_inclusao)', '("' . GeradorEmail::CORINGA_NOME . '", "' . GeradorEmail::CORINGA_EMAIL . '", "127.0.0.1", CURRENT_TIMESTAMP)')
 *		->goAround(true);
 *
 * @author Joubert Guimarães de Assis <joubert@redrat.com.br>
 * @copyright Copyright © 2012, RedRat Consultoria
 * @version 1.0
 */

class GeradorEmail
{
	/**
	 * Quantidade de contatos a ser gerados.
	 *
	 * @var int
	 */
	private $quantidade = 0;

	/**
	 * Delimita se será gerado somente e-mails com o top-level-domain brasileiro ".br".
	 *
	 * @var boolean
	 */
	private $somente_tld_br = false;

	/**
	 * Delimita se será gerado query contínua ou várias querys.
	 *
	 * @var boolean
	 */
	private $query_continua = false;

	/**
	 * Primeira parte da query, composta pelos nomes dos campos.
	 *
	 * @var string
	 */
	private $query_sintaxe;

	/**
	 * Segunda parte da query, composta pelos valores dos campos.
	 *
	 * @var string
	 */
	private $query_valores;

	/**
	 * Dados necessários para a geração randômica dos e-mails.
	 *
	 * @var array
	 */
	private $dicionario;

	/**
	 * Coringa do nome.
	 */
	const CORINGA_NOME = "%LENOME%";

	/**
	 * Coringa do email.
	 */
	const CORINGA_EMAIL = "%EMAILME%";

	/**
	 * Construtor inútil da classe.
	 *
	 * @return void
	 */
	private function __constuct() {}

	/**
	 * Inicia o processo com o instanciamento do objeto.
	 *
	 * @return GeradorEmail Retorna o próprio objeto.
	 */
	public static function iniciar()
	{
		return new self;
	}

	/**
	 * Define a quantidade de e-mails a ser gerados no processo.
	 *
	 * @return GeradorEmail Retorna o próprio objeto.
	 */
	public function setQuantidade($valor)
	{
		if(!is_int($valor))
			exit("Você andou matando aula? Não sabe o que é um numero inteiro?");
		$this->quantidade = $valor;
		return $this;
	}

	/**
	 * Determina o tipo de query que será exibido.
	 *
	 * @return GeradorEmail Retorna o próprio objeto.
	 */
	public function setQueryContinua($valor)
	{
		if(!is_bool($valor))
			exit("Sua anta, eu pedi um bool, não um " . gettype($valor));
		$this->query_continua = $valor;
		return $this;
	}

	/**
	 * Delimita se será gerado somente e-mail com o TDL brasileiro.
	 *
	 * @return GeradorEmail Retorna o próprio objeto.
	 */
	public function setSomenteBr($valor)
	{
		if(!is_bool($valor))
			exit("Sua anta, eu pedi um bool, não um " . gettype($valor));
		$this->somente_tld_br = $valor;
		return $this;
	}

	/**
	 * Recebe as querys que serão usadas na geração.
	 *
	 * @param string $sintaxe Parte sintática da query.
	 * @param string $sintaxe Parte com valores da query.
	 * @return GeradorEmail Retorna o próprio objeto.
	 */
	public function setQuery($sintaxe, $valores)
	{
		$this->query_sintaxe = $sintaxe;
		$this->query_valores = $valores;
		return $this;
	}

	/**
	 * Faz um slug na string.
	 *
	 * @param string $string Texto a ser convertido
	 * @param string $troca_espaco Caracter que será trocado pelo espaço.
	 * @return string Retorna o texto convertido.
	 */
	private function normaliza($string, $troca_espaco = "")
	{
		$listar = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýýþÿŔŕ';
		$trocar = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuuyybyRr';
		return utf8_encode(strtolower(strtr(utf8_decode(str_replace(" ", $troca_espaco, $string)), utf8_decode($listar), $trocar)));
	}

	/**
	 * Coração da classe, gera os dados do nome e do e-mail do contato
	 * por intermédio de um complexo sistema de algoritmos simples.
	 *
	 * @return array Retorna o dado fictício.
	 */
	private function geraEmailFicticio()
	{
		$nome = $this->dicionario["palavras"]["dados"][rand(0, ($this->dicionario["palavras"]["quantidade"] - 1))];
		$sobrenome = rand() % 2 == 0 ? ' ' . $this->dicionario["palavras"]["dados"][rand(0, ($this->dicionario["palavras"]["quantidade"] - 1))] : '';
		$dominio = $this->normaliza($this->dicionario["palavras"]["dados"][rand(0, ($this->dicionario["palavras"]["quantidade"] - 1))]);
		if(rand() % 2 == 0 || $this->somente_tld_br)
			$dominio .= '.' . $this->dicionario["cid"]["dados"][rand(0, ($this->dicionario["cid"]["quantidade"] - 1))] . '.' . ($this->somente_tld_br ? 'br' : $this->dicionario["tld"]["dados"][rand(0, ($this->dicionario["tld"]["quantidade"] - 1))]);
		else
			$dominio .= '.' . $this->dicionario["tld"]["dados"][rand(0, ($this->dicionario["tld"]["quantidade"] - 1))];
		$retorno["nome"] = ucwords($nome) . '' . ucwords($sobrenome);
		$retorno["email"] = $this->normaliza($nome) . ($sobrenome ? '.' . $this->normaliza($sobrenome) : '') . '@' . $dominio;
		return $retorno;
	}

	/**
	 * Carrega as listas de dicionários que será usados para a geração dos dados fictícios.
	 *
	 * @return void
	 */
	private function carregaDicionarios()
	{
		if(!file_exists(__DIR__ . "/lista_palavras_pt-br"))
			exit("Caralho velho, eu não gero palavras do nada não, cade a minha lista de palavras?");
		if(!file_exists(__DIR__ . "/lista_cid"))
			exit("Sua vadia, eu não sou a internet para saber os domínios, cadê a minha lista?");
		if(!file_exists(__DIR__ . "/lista_tld"))
			exit("Sua vadia, eu não sou a internet para saber os domínios, cadê a minha lista?");
		$this->dicionario["palavras"]["dados"] = file(__DIR__ . "/lista_palavras_pt-br", FILE_IGNORE_NEW_LINES);
		$this->dicionario["palavras"]["quantidade"] = count($this->dicionario["palavras"]["dados"]);
		$this->dicionario["cid"]["dados"] = file(__DIR__ . "/lista_cid", FILE_IGNORE_NEW_LINES);
		$this->dicionario["cid"]["quantidade"] = count($this->dicionario["cid"]["dados"]);
		$this->dicionario["tld"]["dados"] = file(__DIR__ . "/lista_tld", FILE_IGNORE_NEW_LINES);
		$this->dicionario["tld"]["quantidade"] = count($this->dicionario["tld"]["dados"]);
	}

	/**
	 * É aqui que a mágica acontece e as querys ou csv são criadas.
	 *
	 * @return void
	 */
	public function goAround($com_pre = false, $gerar_arquivo = false)
	{
		if($this->quantidade == 0)
			exit("Como você pode ser retardado cara? Como eu vou gerar 0 emails?");
		$this->carregaDicionarios();
		
		if($com_pre)
			echo '<pre>';
		if($this->query_sintaxe && $this->query_valores)
		{
			if($gerar_arquivo)
				$nome_sql = 'sql_' . date("Ymd-His") . '.sql';
			if($this->query_continua)
			{
				$i = 0;
				while($i < $this->quantidade)
				{
					$dados = $this->geraEmailFicticio();
					$tudo[] = str_replace(array(self::CORINGA_NOME, self::CORINGA_EMAIL), array($dados["nome"], $dados["email"]), $this->query_valores);
					$i++;	
				}
				echo $this->query_sintaxe . ' VALUES ' . implode(', ', $tudo) . ';';
				if($gerar_arquivo)
					file_put_contents($nome_sql, $this->query_sintaxe . ' VALUES ' . implode(', ', $tudo) . ';');
			}
			else
			{
				$i = 0;
				while($i < $this->quantidade)
				{
					$dados = $this->geraEmailFicticio();
					echo $this->query_sintaxe . ' VALUES ' . str_replace(array(self::CORINGA_NOME, self::CORINGA_EMAIL), array($dados["nome"], $dados["email"]), $this->query_valores) . ';' . "\n";
					if($gerar_arquivo_sql)
						file_put_contents($nome_sql, $this->query_sintaxe . ' VALUES ' . str_replace(array(self::CORINGA_NOME, self::CORINGA_EMAIL), array($dados["nome"], $dados["email"]), $this->query_valores) . ';' . "\n", FILE_APPEND);
					$i++;	
				}
			}
		}
		else
		{
			if($gerar_arquivo)
			{
				$nome_csv = 'csv_' . date("Ymd-His") . '.csv';
				file_put_contents($nome_csv, 'Nome;E-mail' . "\n");
			}
			$i = 0;
			echo 'Nome;E-mail' . "\n";
			while($i < $this->quantidade)
			{
				$dados = $this->geraEmailFicticio();
				echo $dados["nome"] . ';' . $dados["email"] . "\n";
				if($gerar_arquivo)
					file_put_contents($nome_csv, $dados["nome"] . ';' . $dados["email"] . "\n", FILE_APPEND);
				$i++;	
			}
		}
	}
}
