<?php

namespace Bpost\BpostApiClient\Tests\Geo6;

use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidDayException;
use Bpost\BpostApiClient\Geo6\Day;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DayTest extends TestCase
{
    /**
     * Tests Day::createFromXml()
     */
    public function testCreateFromXml()
    {
        $data = array(
            'AMOpen' => '9:00',
            'AMClose' => '12:00',
            'PMOpen' => '13:00',
            'PMClose' => '18:00',
        );

        // build xml
        $xmlString = '<Monday>';
        foreach ($data as $key => $value) {
            $xmlString .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $xmlString .= '</Monday>';
        $xml = simplexml_load_string($xmlString);

        $day = Day::createFromXML($xml);

        $this->assertSame($data['AMOpen'], $day->getAmOpen());
        $this->assertSame($data['AMClose'], $day->getAmClose());
        $this->assertSame($data['PMOpen'], $day->getPmOpen());
        $this->assertSame($data['PMClose'], $day->getPmClose());
    }

    /**
     * Tests Day->getDayIndex()
     */
	#[DataProvider('getDayDataset')]
    public function testGetDayIndex(string $dayName, int $expectedIndex)
    {
        $day = new Day($dayName);
        $this->assertEquals($expectedIndex, $day->getDayIndex());
    }

	public function testGetInvalidDayIndex()
	{
		$this->expectException(BpostInvalidDayException::class);
		(new Day('Test'))->getDayIndex();
	}

	public static function getDayDataset(): array
	{
		return [
			['Monday', 1],
			['Tuesday', 2],
			['Wednesday', 3],
			['Thursday', 4],
			['Friday', 5],
			['Saturday', 6],
			['Sunday', 7],
		];
	}
}
