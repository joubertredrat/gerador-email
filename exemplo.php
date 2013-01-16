<?php
	include('GeradorEmail.class.php');

	GeradorEmail::iniciar()
	->setQuantidade(12005)
	->setQueryContinua(false)
	->setSomenteBr(true)
	->setQuery('INSERT INTO contatos (nome, email, cidade, data_inclusao)', '("' . GeradorEmail::CORINGA_NOME . '", "' . GeradorEmail::CORINGA_EMAIL . '", "Belo Horizonte", CURRENT_TIMESTAMP)')
	->goAround(true);
?>