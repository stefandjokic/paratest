<?php

namespace ParaTest\Logging\JUnit;

use ParaTest\Logging\LogInterpreter;

class WriterTest extends \TestBase
{
    protected $writer;

    /** @var LogInterpreter */
    protected $interpreter;
    protected $passing;

    public function setUp()
    {
        $this->interpreter = new LogInterpreter();
        $this->writer = new Writer($this->interpreter, 'test/fixtures/tests/');
        $this->passing = FIXTURES . DS . 'results' . DS . 'single-passing.xml';
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            'ParaTest\\Logging\\LogInterpreter',
            $this->getObjectValue($this->writer, 'interpreter')
        );
        $this->assertEquals('test/fixtures/tests/', $this->writer->getName());
    }

    public function testSingleFileLog()
    {
        $this->addPassingReader();
        $xml = $this->writer->getXml();
        $this->assertXmlStringEqualsXmlString(file_get_contents($this->passing), $xml);
    }

    public function testMixedFileLog()
    {
        $mixed = FIXTURES . DS . 'results' . DS . 'mixed-results.xml';
        $reader = new Reader($mixed);
        $this->interpreter->addReader($reader);
        $writer = new Writer($this->interpreter, 'test/fixtures/tests/');
        $xml = $writer->getXml();
        $this->assertXmlStringEqualsXmlString(file_get_contents($mixed), $xml);
    }

    public function testWrite()
    {
        $output = FIXTURES . DS . 'logs' . DS . 'passing.xml';
        $this->addPassingReader();
        $this->writer->write($output);
        $this->assertXmlStringEqualsXmlString(file_get_contents($this->passing), file_get_contents($output));
        if (file_exists($output)) {
            unlink($output);
        }
    }

    protected function addPassingReader()
    {
        $reader = new Reader($this->passing);
        $this->interpreter->addReader($reader);
    }

    /**
     * Empty line attributes, e.g. line="" breaks Jenkins parsing since it needs to be an integer.
     * To repair, ensure that empty line attributes are actually written as 0 instead of empty string.
     */
    public function testThatEmptyLineAttributesConvertToZero()
    {
        $mixed = FIXTURES . DS . 'results' . DS . 'junit-example-result.xml';
        $reader = new Reader($mixed);
        $this->interpreter->addReader($reader);
        $writer = new Writer($this->interpreter, 'test/fixtures/tests/');
        $xml = $writer->getXml();

        $this->assertFalse(strpos($xml, 'line=""'), 'Expected no empty line attributes (line=""), but found one.');
    }
}