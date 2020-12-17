<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

namespace Tests\Centreon\Infrastructure\RequestParameters;

use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Centreon\Domain\RequestParameters\Interfaces\RequestParametersInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\RequestParametersTranslatorException;
use PHPUnit\Framework\TestCase;

class SqlRequestParametersTranslatorTest extends TestCase
{
    protected $requestParameters;

    protected function setUp(): void
    {
        $this->requestParameters = $this->createMock(RequestParametersInterface::class);
    }

    /**
     * test translatePaginationToSql first page
     */
    public function testTranslatePaginationToSqlFirstPage()
    {
        $this->requestParameters->expects($this->once())
            ->method('getPage')
            ->willReturn(1);
        $this->requestParameters->expects($this->exactly(2))
            ->method('getLimit')
            ->willReturn(10);
        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);

        $pagination = $sqlRequestParametersTranslator->translatePaginationToSql();

        $this->assertEquals(' LIMIT 0, 10', $pagination);
    }

    /**
     * test translatePaginationToSql second page
     */
    public function testTranslatePaginationToSqlSecondPage()
    {
        $this->requestParameters->expects($this->once())
            ->method('getPage')
            ->willReturn(2);
        $this->requestParameters->expects($this->exactly(2))
            ->method('getLimit')
            ->willReturn(10);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);

        $pagination = $sqlRequestParametersTranslator->translatePaginationToSql();

        $this->assertEquals(' LIMIT 10, 10', $pagination);
    }

    /**
     * test translateSearchParameterToSql with matching concordance array
     */
    public function testTranslateSearchParameterToSqlWithMatchingConcordanceArray()
    {
        $this->requestParameters->expects($this->once())
            ->method('getSearch')
            ->willReturn([
                '$or' => [
                    'host.name' => ['$rg' => 'host1'],
                    'host.description' => ['$rg' => 'host1'],
                ]
            ]);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);
        $sqlRequestParametersTranslator->setConcordanceArray([
            'host.name' => 'h.name',
            'host.description' => 'h.description',
        ]);

        $search = $sqlRequestParametersTranslator->translateSearchParameterToSql();

        $this->assertEquals(
            ' WHERE (h.name REGEXP :value_1 OR h.description REGEXP :value_2)',
            $search
        );
    }

    /**
     * test translateSearchParameterToSql with wrong concordance array and strict mode with exception
     */
    public function testTranslateSearchParameterToSqlWithWrongConcordanceArrayAndStrictModeException()
    {
        $this->requestParameters->expects($this->once())
            ->method('getSearch')
            ->willReturn([
                '$or' => [
                    'host.name' => ['$rg' => 'host1'],
                    'host.description' => ['$rg' => 'host1'],
                ]
            ]);
        $this->requestParameters->expects($this->exactly(2))
            ->method('getConcordanceStrictMode')
            ->willReturn(RequestParameters::CONCORDANCE_MODE_STRICT);
        $this->requestParameters->expects($this->once())
            ->method('getConcordanceErrorMode')
            ->willReturn(RequestParameters::CONCORDANCE_ERRMODE_EXCEPTION);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);
        $sqlRequestParametersTranslator->setConcordanceArray([
            'host.name' => 'h.name',
            'host.alias' => 'h.description',
        ]);

        $this->expectException(RequestParametersTranslatorException::class);
        $this->expectExceptionMessage("The parameter host.description is not allowed");

        $sqlRequestParametersTranslator->translateSearchParameterToSql();
    }

    /**
     * test translateSearchParameterToSql with wrong concordance array and strict mode in silent mode
     */
    public function testTranslateSearchParameterToSqlWithWrongConcordanceArrayAndStrictModeSilent()
    {
        $this->requestParameters->expects($this->once())
            ->method('getSearch')
            ->willReturn([
                '$or' => [
                    'host.name' => ['$rg' => 'host1'],
                    'host.description' => ['$rg' => 'host1'],
                ]
            ]);
        $this->requestParameters->expects($this->exactly(2))
            ->method('getConcordanceStrictMode')
            ->willReturn(RequestParameters::CONCORDANCE_MODE_STRICT);
        $this->requestParameters->expects($this->once())
            ->method('getConcordanceErrorMode')
            ->willReturn(RequestParameters::CONCORDANCE_ERRMODE_SILENT);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);
        $sqlRequestParametersTranslator->setConcordanceArray([
            'host.name' => 'h.name',
            'host.alias' => 'h.description',
        ]);

        $search = $sqlRequestParametersTranslator->translateSearchParameterToSql();

        $this->assertEquals(
            ' WHERE (h.name REGEXP :value_1)',
            $search
        );
    }

    /**
     * test translateSearchParameterToSql with normalizers
     */
    public function testTranslateSearchParametersWithNormalizers()
    {
        $this->requestParameters->expects($this->once())
            ->method('getSearch')
            ->willReturn([
                '$and' => [
                    'event.type' => ['$eq' => 'comment'],
                    'event.date' => ['$ge' => '2020-01-31T03:54:12+01:00'],
                ]
            ]);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);
        $sqlRequestParametersTranslator->setConcordanceArray([
            'event.type' => 'e.type',
            'event.date' => 'e.date',
        ]);
        $sqlRequestParametersTranslator->addNormalizer(
            'event.date',
            new class implements NormalizerInterface
            {
                public function normalize($valueToNormalize)
                {
                    return (new \Datetime($valueToNormalize))->getTimestamp();
                }
            }
        );

        $search = $sqlRequestParametersTranslator->translateSearchParameterToSql();

        $this->assertEquals(
            ' WHERE (e.type = :value_1 AND e.date >= :value_2)',
            $search
        );

        $this->assertEquals(
            [
                ':value_1' => [ \PDO::PARAM_STR => 'comment' ],
                ':value_2' => [ \PDO::PARAM_INT => 1580439252 ],
            ],
            $sqlRequestParametersTranslator->getSearchValues()
        );
    }

    /**
     * test translateSortParameterToSql
     */
    public function testTranslateSortParameterToSql()
    {
        $this->requestParameters->expects($this->once())
            ->method('getSort')
            ->willReturn([
                'host.name' => 'ASC',
                'host.alias' => 'DESC',
            ]);
        $this->requestParameters->expects($this->exactly(1))
            ->method('getPage')
            ->willReturn(1);
        $this->requestParameters->expects($this->exactly(2))
            ->method('getLimit')
            ->willReturn(10);

        $sqlRequestParametersTranslator = new SqlRequestParametersTranslator($this->requestParameters);
        $sqlRequestParametersTranslator->setConcordanceArray([
            'host.name' => 'h.name',
            'host.alias' => 'h.description',
        ]);

        $this->assertEquals(
            ' LIMIT 0, 10',
            $sqlRequestParametersTranslator->translatePaginationToSql()
        );

        $this->assertEquals(
            ' ORDER BY h.name IS NULL, h.name ASC, h.description IS NULL, h.description DESC',
            $sqlRequestParametersTranslator->translateSortParameterToSql()
        );
    }
}
