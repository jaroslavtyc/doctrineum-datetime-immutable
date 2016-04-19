<?php
namespace Doctrineum\DateTimeImmutable;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\Type;

/**
 * @method static DateTimeImmutableType getType($name)
 */
class DateTimeImmutableType extends AbstractSelfRegisteringType
{

    const DATETIME_IMMUTABLE = 'datetime_immutable';

    /**
     * @var DateTimeType
     */
    private $dateTimeType;

    /**
     * @inheritdoc
     */
    public static function getTypeName()
    {
        return self::DATETIME_IMMUTABLE;
    }

    /**
     * @inheritdoc
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $this->getDateTimeType()->getSQLDeclaration($fieldDeclaration, $platform);
    }

    /**
     * @return DateTimeType
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDateTimeType()
    {
        if ($this->dateTimeType === null) {
            $this->dateTimeType = parent::getType(Type::DATETIME);
        }

        return $this->dateTimeType;
    }

    /**
     * @inheritdoc
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $this->getDateTimeType()->convertToDatabaseValue($value, $platform);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $val = \DateTimeImmutable::createFromFormat($platform->getDateTimeFormatString(), $value);

        if (!$val) {
            try {
                $val = date_create_immutable($value);
            } catch (\Exception $exception) { // due to HHVM behavior
                $val = null; // exception will be thrown bellow
            }
        }

        if (!$val) {
            throw Exceptions\ConversionFailed::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $val;
    }

}
