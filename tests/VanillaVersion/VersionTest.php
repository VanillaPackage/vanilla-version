<?php

namespace Rentalhost\VanillaVersion;

use PHPUnit_Framework_TestCase;

class VersionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test versions basic methods.
     * @covers Rentalhost\VanillaVersion\Version::__construct
     * @covers Rentalhost\VanillaVersion\Version::getProcessedVersion
     * @covers Rentalhost\VanillaVersion\Version::getMajorVersion
     * @covers Rentalhost\VanillaVersion\Version::getMinorVersion
     * @covers Rentalhost\VanillaVersion\Version::getPatchVersion
     * @covers Rentalhost\VanillaVersion\Version::getReleaseVersion
     * @covers Rentalhost\VanillaVersion\Version::getMetadataVersion
     * @covers Rentalhost\VanillaVersion\Version::get
     * @covers Rentalhost\VanillaVersion\Version::getNumber
     * @covers Rentalhost\VanillaVersion\Version::__toString
     * @dataProvider dataBasic
     */
    public function testBasic($version, $expectedMajor, $expectedMinor, $expectedPatch, $expectedRelease, $expectedMetadata, $expectedNumber)
    {
        $versionInstance = new Version($version);

        $this->assertSame($expectedMajor, $versionInstance->getMajorVersion());
        $this->assertSame($expectedMinor, $versionInstance->getMinorVersion());
        $this->assertSame($expectedPatch, $versionInstance->getPatchVersion());
        $this->assertSame($expectedRelease, $versionInstance->getReleaseVersion());
        $this->assertSame($expectedMetadata, $versionInstance->getMetadataVersion());

        $this->assertSame($version, $versionInstance->get());
        $this->assertSame($version, (string) $versionInstance);

        $this->assertSame($expectedNumber, $versionInstance->getNumber());
    }

    public function dataBasic()
    {
        return [
            [ "1.0.0",                   1,   0,  0, null,        null,          10000 ],
            [ "2.1.3",                   2,   1,  3, null,        null,          20103 ],
            [ "11.12.13",                11, 12, 13, null,        null,          111213 ],
            [ "1.0.0-pre-alpha",         1,   0,  0, "pre-alpha", null,          10000 ],
            [ "1.0.0-alpha",             1,   0,  0, "alpha",     null,          10000 ],
            [ "1.0.0-beta+dev",          1,   0,  0, "beta",      "dev",         10000 ],
            [ "1.0.0-dev+1.0.1",         1,   0,  0, "dev",       "1.0.1",       10000 ],
            [ "1.0.0-canary+1234567890", 1,   0,  0, "canary",    "1234567890",  10000 ],
            [ "1.0.0-nightly+1+2+3",     1,   0,  0, "nightly",   "1+2+3",       10000 ],
            [ "1.0.0+1.0.1",             1,   0,  0, null,        "1.0.1",       10000 ],
            [ "1.0.0+1.0.1-patch",       1,   0,  0, null,        "1.0.1-patch", 10000 ],
            [ "1.0.0+-",                 1,   0,  0, null,        "-",           10000 ],
        ];
    }

    /**
     * Test version comparison.
     * It considers only the version major, minor and patch.
     * @covers Rentalhost\VanillaVersion\Version::compare
     * @dataProvider dataCompare
     */
    public function testCompare($leftVersion, $rightVersion, $operator, $expectedReturn)
    {
        $leftVersion = new Version($leftVersion);

        $this->assertSame($expectedReturn, $leftVersion->compare($rightVersion, $operator));
        $this->assertSame($expectedReturn, $leftVersion->compare(new Version($rightVersion), $operator));
    }

    public function dataCompare()
    {
        return [
            // Lower than first.
            [ "1.0.0", "0.9.0", "<",   false ],
            [ "1.0.0", "0.9.0", "<=",  false ],
            [ "1.0.0", "0.9.0", ">",   true ],
            [ "1.0.0", "0.9.0", ">=",  true ],
            [ "1.0.0", "0.9.0", "==",  false ],
            [ "1.0.0", "0.9.0", "!=",  true ],
            [ "1.0.0", "0.9.0", "<>",  true ],

            // Greater than first.
            [ "1.0.0", "1.9.0", "<",   true ],
            [ "1.0.0", "1.9.0", "<=",  true ],
            [ "1.0.0", "1.9.0", ">",   false ],
            [ "1.0.0", "1.9.0", ">=",  false ],
            [ "1.0.0", "1.9.0", "==",  false ],
            [ "1.0.0", "1.9.0", "!=",  true ],
            [ "1.0.0", "1.9.0", "<>",  true ],

            // Equals to first.
            [ "1.0.0", "1.0.0", "<",   false ],
            [ "1.0.0", "1.0.0", "<=",  true ],
            [ "1.0.0", "1.0.0", ">",   false ],
            [ "1.0.0", "1.0.0", ">=",  true ],
            [ "1.0.0", "1.0.0", "==",  true ],
            [ "1.0.0", "1.0.0", "!=",  false ],
            [ "1.0.0", "1.0.0", "<>",  false ],
        ];
    }

    /**
     * Capture InvalidVersionException.
     * @dataProvider dataInvalidVersionException
     */
    public function testInvalidVersionException($version)
    {
        $this->setExpectedException(Exception\InvalidVersionException::class);
        new Version($version);
    }

    public function dataInvalidVersionException()
    {
        return [
            [ "a.b.c" ],
            [ "111.0.0" ],
            [ "1.0.0-" ],
            [ "1.0.0+" ],
            [ "1.0.0-+" ],
            [ "1.0" ],
        ];
    }

    public function testInvalidOperatorException()
    {
        $this->setExpectedException(Exception\InvalidOperatorException::class);
        (new Version("1.0.0"))->compare("2.0.0", "invalid");
    }
}
