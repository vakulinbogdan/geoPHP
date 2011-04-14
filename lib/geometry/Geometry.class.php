<?php
/*
 * (c) Camptocamp <info@camptocamp.com>
 * (c) Patrick Hayes
 *
 * This code is open-source and licenced under the Modified BSD License.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Geometry : abstract class which represents a geometry.
 *
 * @package    sfMapFishPlugin
 * @subpackage GeoJSON
 * @author     Camptocamp <info@camptocamp.com>
 * @version    
 */
abstract class Geometry 
{
	private   $geos = NULL;
  protected $geom_type;
  protected $srid;

	
  // Abtract: Standard
  // -----------------
	abstract public function area();
	abstract public function boundary();
  abstract public function centroid();
	abstract public function length();
	abstract public function y();
	abstract public function x();
	abstract public function numGeometries();
	abstract public function geometryN($n);
  abstract public function startPoint(); 
	abstract public function endPoint();
	abstract public function isRing();            // Mssing dependancy
	abstract public function isClosed();          // Missing dependancy
	abstract public function numPoints();
	abstract public function pointN($n);
	abstract public function exteriorRing();
	abstract public function numInteriorRings();
  abstract public function interiorRingN($n);
	abstract public function dimension();
	
  // Abtract: Non-Standard
  // ---------------------
  abstract public function getCoordinates();
  abstract public function getBBox();


  // Public: Standard -- Common to all geometries
  // --------------------------------------------
  public function getSRID() {
  	return $this->srid;
  }
  
	public function setSRID($srid) {
		if ($this->geos()) {
			$this->geos()->setSRID($srid);
		}
		$this->srid = $srid;
	}
	
  public function hasZ() {
	  // geoPHP does not support Z values at the moment
	  return FALSE;	
	}

  public function is3D() {
	  // geoPHP does not support 3D geometries at the moment
	  return FALSE;	
	}

	public function isMeasured() {
  	// geoPHP does not yet support M values
  	return FALSE;
  }
	
	public function isEmpty() {
  	// geoPHP does not yet support empty geometries
  	return FALSE;
  }

	public function coordinateDimension() {
		// geoPHP only supports 2-dimentional space
		return 2;
	}
  
  public function envelope() {
  	if ($this->geom) {
  		return geoPHP::load($this->geos->envelope(),'wkt');
  	}
  	
  	$bbox = $this->getBBox();
  	$points = array (
  	  new Point($bbox['maxy'],$bbox['minx']),
  	  new Point($bbox['maxy'],$bbox['maxx']),
  	  new Point($bbox['miny'],$bbox['maxx']),
  	  new Point($bbox['miny'],$bbox['minx']),
  	  new Point($bbox['maxy'],$bbox['minx']),
  	);
  	$outer_boundary = new LinearRing($points);
  	return new Polygon(array($outer_boundary));
  }

  
  // Public: Non-Standard -- Common to all geometries
  // ------------------------------------------------
  public function getGeomType() {
    return $this->geom_type;
  }

  public function getGeoInterface() {
    return array(
      'type'=> $this->getGeomType(),
      'coordinates'=> $this->getCoordinates()
    );
  }
  
  public function geos() {
  	// If it's already been set, just return it
  	if ($this->geos !== NULL) {
  		return $this->geos;
  	}
  	// It hasn't been set yet, generate it
  	if (class_exists('GEOSGeometry')) {
	  	$reader = new GEOSWKTReader();
	    $this->geos = $reader->read($this->out('wkt'));
  	}
  	else {
  		$this->geos = FALSE;
    }
    return $this->geos;
  }

  public function setGeos($geos) {
  	$this->geos = $geos;
  }

  public function out($format) {
    $type_map = geoPHP::getAdapterMap();
    $processor_type = $type_map[$format];
    $processor = new $processor_type();
    $value = $processor->write($this);
    return $value;
  }
  
  
  // Public: Aliases
  // ---------------
  public function getCentroid() {
  	return $this->centroid();
  }
  
  public function getArea() {
    return $this->area();
  }

	public function getX() {
		return $this->x();
	}
	
	public function getY() {
		return $this->y();
	}

	public function getGeos() {
		return $this->geos();
	}
	
  // Public: GEOS Only Functions
  // ---------------------------
	public function pointOnSurface() {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->pointOnSurface(),'wkt');
		}
	}
	
	public function equals($geometry) {
		if ($this->geos()) {
			return $this->geos()->equals($geometry->geos());
		}
	}
	
	public function equalsExact($geometry) {
		if ($this->geos()) {
			return $this->geos()->equalsExact($geometry->geos());
		}
	}
	
  public function relate($geometry) {
  	//@@TODO: Deal with second "pattern" parameter
		if ($this->geos()) {
			return $this->geos()->relate($geometry->geos());
		}
  }
  
	public function checkValidity() {
		if ($this->geos()) {
			return $this->geos()->checkValidity();
		}
  }

	public function isSimple() {
		if ($this->geos()) {
			return $this->geos()->isSimple();
		}
  }
  
	public function project($srid) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->project($srid),'wkt');
		}
	}
	
	public function buffer($distance, $style_array = FASLSE) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->buffer($distance, $style_array),'wkt');
		}
	}
	
	public function intersection($geometry) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->intersection($geometry->geom),'wkt');
		}
	}
	
	public function convexHull() {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->convexHull(),'wkt');
		}
	}
	
	public function difference($geometry) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->difference($geometry->geom),'wkt');
		}
	}
	
	public function symDifference($geometry) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->symDifference($geometry->geom),'wkt');
		}
	}
	
	public function union($geometry) {
		//@@TODO: also does union cascade
		if ($this->geos()) {
			return geoPHP::load($this->geos()->union($geometry->geom),'wkt');
		}
	}
	
	public function simplify($tolerance, $preserveTopology = FALSE) {
		if ($this->geos()) {
			return geoPHP::load($this->geos()->simplify($tolerance, $preserveTopology),'wkt');
		}
	}
	
	public function disjoint($geometry) {
		if ($this->geos()) {
			return $this->geos()->disjoint($geometry->geos());
		}
	}
	
	public function touches($geometry) {
		if ($this->geos()) {
			return $this->geos()->touches($geometry->geos());
		}
	}
	
	public function intersects($geometry) {
		if ($this->geos()) {
			return $this->geos()->intersects($geometry->geos());
		}
	}
	
	public function crosses($geometry) {
		if ($this->geos()) {
			return $this->geos()->crosses($geometry->geos());
		}
	}

	public function within($geometry) {
		if ($this->geos()) {
			return $this->geos()->within($geometry->geos());
		}
	}
	
	public function contains($geometry) {
		if ($this->geos()) {
			return $this->geos()->contains($geometry->geos());
		}
	}
	
	public function overlaps($geometry) {
		if ($this->geos()) {
			return $this->geos()->overlaps($geometry->geos());
		}
	}
	
	public function covers($geometry) {
		if ($this->geos()) {
			return $this->geos()->covers($geometry->geos());
		}
	}
	
	public function coveredBy($geometry) {
		if ($this->geos()) {
			return $this->geos()->coveredBy($geometry->geos());
		}
	}

	public function distance($geometry) {
		if ($this->geos()) {
			return $this->geos()->distance($geometry->geos());
		}
	}
	
	public function hausdorffDistance($geometry) {
		if ($this->geos()) {
			return $this->geos()->hausdorffDistance($geometry->geos());
		}
	}
	
}
