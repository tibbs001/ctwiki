<?php

namespace Wikibase\DataModel\Snak;

use Comparable;
use Hashable;
use InvalidArgumentException;
use Traversable;
use Wikibase\DataModel\HashArray;
use Wikibase\DataModel\Internal\MapValueHasher;

/**
 * List of Snak objects.
 * Indexes the snaks by hash and ensures no more the one snak with the same hash are in the list.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Addshore
 */
class SnakList extends HashArray implements Comparable, Hashable {

	/**
	 * @param Snak[]|Traversable $snaks
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $snaks = [] ) {
		if ( !is_array( $snaks ) && !( $snaks instanceof Traversable ) ) {
			throw new InvalidArgumentException( '$snaks must be an array or an instance of Traversable' );
		}

		foreach ( $snaks as $index => $snak ) {
			$this->setElement( $index, $snak );
		}
	}

	/**
	 * @see GenericArrayObject::getObjectType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getObjectType() {
		return Snak::class;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 *
	 * @return boolean
	 */
	public function hasSnakHash( $snakHash ) {
		return $this->hasElementHash( $snakHash );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 */
	public function removeSnakHash( $snakHash ) {
		$this->removeByElementHash( $snakHash );
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 *
	 * @return boolean Indicates if the snak was added or not.
	 */
	public function addSnak( Snak $snak ) {
		return $this->addElement( $snak );
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 *
	 * @return boolean
	 */
	public function hasSnak( Snak $snak ) {
		return $this->hasElementHash( $snak->getHash() );
	}

	/**
	 * @since 0.1
	 *
	 * @param Snak $snak
	 */
	public function removeSnak( Snak $snak ) {
		$this->removeByElementHash( $snak->getHash() );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $snakHash
	 *
	 * @return Snak|bool
	 */
	public function getSnak( $snakHash ) {
		return $this->getByElementHash( $snakHash );
	}

	/**
	 * @see Comparable::equals
	 *
	 * The comparison is done purely value based, ignoring the order of the elements in the array.
	 *
	 * @since 0.3
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->getHash() === $target->getHash();
	}

	/**
	 * @see Hashable::getHash
	 *
	 * The hash is purely value based. Order of the elements in the array is not held into account.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHash() {
		$hasher = new MapValueHasher();
		return $hasher->hash( $this );
	}

	/**
	 * Groups snaks by property, and optionally orders them.
	 *
	 * @param string[] $order List of property ID strings to order by. Snaks with other properties
	 *  will also be grouped, but put at the end, in the order each property appeared first in the
	 *  original list.
	 *
	 * @since 0.5
	 */
	public function orderByProperty( array $order = [] ) {
		$byProperty = array_fill_keys( $order, [] );

		/** @var Snak $snak */
		foreach ( $this as $snak ) {
			$byProperty[$snak->getPropertyId()->getSerialization()][] = $snak;
		}

		$ordered = [];
		foreach ( $byProperty as $snaks ) {
			$ordered = array_merge( $ordered, $snaks );
		}

		$this->exchangeArray( $ordered );

		$index = 0;
		foreach ( $ordered as $snak ) {
			$this->offsetHashes[$snak->getHash()] = $index++;
		}
	}

}
