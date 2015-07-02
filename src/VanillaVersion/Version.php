<?php

namespace Rentalhost\VanillaVersion;

class Version
{
    /**
     * Version match rule, base on [semver.org].
     * @type string
     */
    private static $VERSION_MATCH = '/^
        (?<major>\d{1,2})\.
        (?<minor>\d{1,2})\.
        (?<patch>\d{1,2})
        (?:-(?<release>[^\+]+))?
        (?:\+(?<metadata>.+))?
    $/x';

    /**
     * Stores version.
     * @var string
     */
    private $version;

    /**
     * Stores version processed.
     * @var array[string, int|string|null]
     */
    private $versionProcessed;

    /**
     * Construct a new version instance.
     * @param string $version Version.
     */
    public function __construct($version)
    {
        $this->version = $version;
        $this->versionProcessed = self::getProcessedVersion($version);
    }

    /**
     * Get major version.
     * @return integer
     */
    public function getMajorVersion()
    {
        return $this->versionProcessed["major"];
    }

    /**
     * Get minor version.
     * @return integer
     */
    public function getMinorVersion()
    {
        return $this->versionProcessed["minor"];
    }

    /**
     * Get patch version.
     * @return integer
     */
    public function getPatchVersion()
    {
        return $this->versionProcessed["patch"];
    }

    /**
     * Get release version.
     * @return string|null
     */
    public function getReleaseVersion()
    {
        return $this->versionProcessed["release"];
    }

    /**
     * Get metadata version.
     * @return string|null
     */
    public function getMetadataVersion()
    {
        return $this->versionProcessed["metadata"];
    }

    /**
     * Get version.
     * @return string
     */
    public function get()
    {
        return $this->version;
    }

    /**
     * Get version.
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }

    /**
     * Get version as number.
     * @return int
     */
    public function getNumber()
    {
        return
            $this->versionProcessed["major"] * 10000 +
            $this->versionProcessed["minor"] * 100 +
            $this->versionProcessed["patch"];
    }

    /**
     * Compare with other version.
     * @param string|Version $rightVersion Right-side version to compare.
     * @param string         $operator     Operator to compare.
     * @throws Exception\InvalidOperatorException If an invalid operator is used.
     * @return boolean
     */
    public function compare($rightVersion, $operator)
    {
        $rightVersionProcessed = $rightVersion instanceof self ? $rightVersion : new self($rightVersion);

        $leftVersionNumber = $this->getNumber();
        $rightVersionNumber = $rightVersionProcessed->getNumber();

        switch (strtolower($operator)) {
            case "<":
                return $leftVersionNumber < $rightVersionNumber;
                break;

            case "<=":
                return $leftVersionNumber <= $rightVersionNumber;
                break;

            case ">":
                return $leftVersionNumber > $rightVersionNumber;
                break;

            case ">=":
                return $leftVersionNumber >= $rightVersionNumber;
                break;

            case "==":
                return $leftVersionNumber === $rightVersionNumber;
                break;

            case "!=":
            case "<>":
                return $leftVersionNumber !== $rightVersionNumber;
                break;

            default:
                throw new Exception\InvalidOperatorException("invalid operator");
                break;
        }
    }

    /**
     * Process version data.
     * @param string $version Version to process.
     * @throws Exception\InvalidVersionException If version is invalid.
     * @return array[string, int|string|null]
     */
    private static function getProcessedVersion($version)
    {
        if (!preg_match(self::$VERSION_MATCH, $version, $versionProcessed)) {
            throw new Exception\InvalidVersionException("invalid version");
        }

        return [
            "major" => intval($versionProcessed["major"]),
            "minor" => intval($versionProcessed["minor"]),
            "patch" => intval($versionProcessed["patch"]),
            "release" => empty($versionProcessed["release"]) ? null : $versionProcessed["release"],
            "metadata" => empty($versionProcessed["metadata"]) ? null : $versionProcessed["metadata"],
        ];
    }
}
