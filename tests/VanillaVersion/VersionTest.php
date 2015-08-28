<?php

namespace Rentalhost\VanillaVersion;

use PHPUnit_Framework_TestCase;

/**
 * Class VersionTest
 * @package Rentalhost\VanillaVersion
 */
class VersionTest extends PHPUnit_Framework_TestCase
{
    /** @noinspection PhpTooManyParametersInspection */
    /**
     * Test versions basic methods.
     *
     * @param string $version          Version to parse.
     * @param int    $expectedMajor    Expected major version.
     * @param int    $expectedMinor    Expected minor version.
     * @param int    $expectedPatch    Expected patch version.
     * @param int    $expectedRelease  Expected release version.
     * @param int    $expectedMetadata Expected metadata version.
     * @param int    $expectedNumber   Expected version to number format.
     *
     * @covers       Rentalhost\VanillaVersion\Version::__construct
     * @covers       Rentalhost\VanillaVersion\Version::getProcessedVersion
     * @covers       Rentalhost\VanillaVersion\Version::getMajorVersion
     * @covers       Rentalhost\VanillaVersion\Version::getMinorVersion
     * @covers       Rentalhost\VanillaVersion\Version::getPatchVersion
     * @covers       Rentalhost\VanillaVersion\Version::getReleaseVersion
     * @covers       Rentalhost\VanillaVersion\Version::getMetadataVersion
     * @covers       Rentalhost\VanillaVersion\Version::get
     * @covers       Rentalhost\VanillaVersion\Version::getNumber
     * @covers       Rentalhost\VanillaVersion\Version::__toString
     * @dataProvider dataBasic
     */
    public function testBasic($version, $expectedMajor, $expectedMinor, $expectedPatch, $expectedRelease, $expectedMetadata,
        $expectedNumber)
    {
        $versionInstance = new Version($version);

        static::assertSame($expectedMajor, $versionInstance->getMajorVersion());
        static::assertSame($expectedMinor, $versionInstance->getMinorVersion());
        static::assertSame($expectedPatch, $versionInstance->getPatchVersion());
        static::assertSame($expectedRelease, $versionInstance->getReleaseVersion());
        static::assertSame($expectedMetadata, $versionInstance->getMetadataVersion());

        static::assertSame($version, $versionInstance->get());
        static::assertSame($version, (string) $versionInstance);

        static::assertSame($expectedNumber, $versionInstance->getNumber());
    }

    /**
     * @return array
     */
    public function dataBasic()
    {
        return [
            [ '1.0.0', 1, 0, 0, null, null, 10000 ],
            [ '2.1.3', 2, 1, 3, null, null, 20103 ],
            [ '11.12.13', 11, 12, 13, null, null, 111213 ],
            [ '1.0.0-pre-alpha', 1, 0, 0, 'pre-alpha', null, 10000 ],
            [ '1.0.0-alpha', 1, 0, 0, 'alpha', null, 10000 ],
            [ '1.0.0-beta+dev', 1, 0, 0, 'beta', 'dev', 10000 ],
            [ '1.0.0-dev+1.0.1', 1, 0, 0, 'dev', '1.0.1', 10000 ],
            [ '1.0.0-canary+1234567890', 1, 0, 0, 'canary', '1234567890', 10000 ],
            [ '1.0.0-nightly+1+2+3', 1, 0, 0, 'nightly', '1+2+3', 10000 ],
            [ '1.0.0+1.0.1', 1, 0, 0, null, '1.0.1', 10000 ],
            [ '1.0.0+1.0.1-patch', 1, 0, 0, null, '1.0.1-patch', 10000 ],
            [ '1.0.0+-', 1, 0, 0, null, '-', 10000 ],
        ];
    }

    /**
     * Test version comparison.
     * It considers only the version major, minor and patch.
     *
     * @param string|Version $leftVersion    Left version.
     * @param string|Version $rightVersion   Right version.
     * @param string         $operator       Operator to apply.
     * @param boolean        $expectedReturn Expected operator conclusion.
     *
     * @covers       Rentalhost\VanillaVersion\Version::compare
     * @dataProvider dataCompare
     *
     * @throws Exception\InvalidOperatorException
     */
    public function testCompare($leftVersion, $rightVersion, $operator, $expectedReturn)
    {
        $leftVersion = new Version($leftVersion);

        static::assertSame($expectedReturn, $leftVersion->compare($rightVersion, $operator));
        static::assertSame($expectedReturn, $leftVersion->compare(new Version($rightVersion), $operator));
    }

    /**
     * @return array
     */
    public function dataCompare()
    {
        return [
            // Lower than first.
            [ '1.0.0', '0.9.0', '<', false ],
            [ '1.0.0', '0.9.0', '<=', false ],
            [ '1.0.0', '0.9.0', '>', true ],
            [ '1.0.0', '0.9.0', '>=', true ],
            [ '1.0.0', '0.9.0', '==', false ],
            [ '1.0.0', '0.9.0', '!=', true ],
            [ '1.0.0', '0.9.0', '<>', true ],
            // Greater than first.
            [ '1.0.0', '1.9.0', '<', true ],
            [ '1.0.0', '1.9.0', '<=', true ],
            [ '1.0.0', '1.9.0', '>', false ],
            [ '1.0.0', '1.9.0', '>=', false ],
            [ '1.0.0', '1.9.0', '==', false ],
            [ '1.0.0', '1.9.0', '!=', true ],
            [ '1.0.0', '1.9.0', '<>', true ],
            // Equals to first.
            [ '1.0.0', '1.0.0', '<', false ],
            [ '1.0.0', '1.0.0', '<=', true ],
            [ '1.0.0', '1.0.0', '>', false ],
            [ '1.0.0', '1.0.0', '>=', true ],
            [ '1.0.0', '1.0.0', '==', true ],
            [ '1.0.0', '1.0.0', '!=', false ],
            [ '1.0.0', '1.0.0', '<>', false ],
        ];
    }

    /**
     * Capture InvalidVersionException.
     *
     * @param string $version Version that will throws this exception.
     *
     * @dataProvider dataInvalidVersionException
     */
    public function testInvalidVersionException($version)
    {
        static::setExpectedException(Exception\InvalidVersionException::class);
        new Version($version);
    }

    /**
     * @return array
     */
    public function dataInvalidVersionException()
    {
        return [
            [ 'a.b.c' ],
            [ '111.0.0' ],
            [ '1.0.0-' ],
            [ '1.0.0+' ],
            [ '1.0.0-+' ],
            [ '1.0' ],
        ];
    }

    /**
     * Capture InvalidOperatorException.
     */
    public function testInvalidOperatorException()
    {
        static::setExpectedException(Exception\InvalidOperatorException::class);
        (new Version('1.0.0'))->compare('2.0.0', 'invalid');
    }
}
