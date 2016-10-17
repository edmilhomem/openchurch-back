<?php
/**
 * Created by PhpStorm.
 * User: Jackson
 * Date: 20/03/2016
 * Time: 11:45
 */

use OpenChurch\QueryParameterParser;

class QueryParameterParserTest extends PHPUnit_Framework_TestCase {
    public function testQuerySimples() {

        $parser = new QueryParameterParser();

        $q = 'pessoa.nome__eq:jackson';
        $c = $parser->parse($q);

        $this->assertArrayHasKey('query', $c);
    }
}