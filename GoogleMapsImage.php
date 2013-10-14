<?php

/**
 * New BSD License
 * ---------------
 *
 * Copyright (c) 2013, Radek Hřebeček <rhrebecek@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the "Eciovni" nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace RHrebecek;

use Nette\Image,
    Nette\InvalidStateException,
    Nette\Http\Url;

class GoogleMapsImage extends \Nette\Object
{

    /**
     * @var string
     */
    const URL = 'http://maps.googleapis.com/maps/api/staticmap';

    /** @var string */
    const IMAGE_FORMAT_PNG = 'png',
          IMAGE_FORMAT_PNG32 = 'png32',
          IMAGE_FORMAT_GIF = 'gif',
          IMAGE_FORMAT_JPG = 'jpg',
          IMAGE_FORMAT_JPG_BASELINE = 'jpg-baseline';

    /** @var string */
    const MAP_TYPE_ROADMAP = 'roadmap',
          MAP_TYPE_SATELLITE = 'satellite',
          MAP_TYPE_TERRAIN = 'terrain',
          MAP_TYPE_HYBRID = 'hybrid';


    /** @var \Nette\Http\Url */
    private $url;

    /**
     * Location Parameters
     *
     * @var array
     */
    protected $locationParameters = array(
        // defines the center of the map, equidistant from all edges of the map.
        // This parameter takes a location as either a comma-separated {latitude,longitude}
        // pair (e.g. "40.714728,-73.998672") or a string address (e.g. "city hall, new york, ny")
        // identifying a unique location on the face of the earth
        'center' => NULL,

        //  if markers not present) defines the zoom level of the map, which determines the magnification level of the map.
        //  This parameter takes a numerical value corresponding to the zoom level of the region desired.
        'zoom' => 10,
    );

    /**
     * Map Parameters
     *
     * @var array
     */
    protected $mapParameters = array(
        // (required) defines the rectangular dimensions of the map image.
        // This parameter takes a string of the form {horizontal_value}x{vertical_value}.
        // For example, 500x400 defines a map 500 pixels wide by 400 pixels high.
        // Maps smaller than 180 pixels in width will display a reduced-size Google logo.
        // This parameter is affected by the scale parameter, described below; the final
        // output size is the product of the size and scale values.
        'size' => '500x400',

        // defines the format of the resulting image. By default, the Static Maps API creates PNG images.
        // There are several possible formats including GIF, JPEG and PNG types. Which format you use depends
        // on how you intend to present the image. JPEG typically provides greater compression, while GIF and PNG provide
        // greater detail.
        'format' => self::IMAGE_FORMAT_PNG,

        // defines the type of map to construct. There are several possible maptype values, including roadmap, satellite, hybrid, and terrain.
        'maptype' => self::MAP_TYPE_ROADMAP,

        // defines the language to use for display of labels on map tiles. Note that this parameter is only supported for some country tiles; if the specific language requested is not supported for the tile set, then the default language for that tileset will be used.
        'language' => NULL,

        // defines the appropriate borders to display, based on geo-political sensitivities. Accepts a region code specified as a two-character ccTLD ('top-level domain') value.
        'region' => NULL,
    );

    /**
     * Feature Parameters
     *
     * @var array
     */
    protected $featureParameters = array(
        // efine one or more markers to attach to the image at specified locations.
        // This parameter takes a single marker definition with parameters separated by the pipe character (|).
        // Multiple markers may be placed within the same markers parameter as long as they exhibit the same style;
        // you may add additional markers of differing styles by adding additional markers parameters.
        // Note that if you supply markers for a map, you do not need to specify the (normally required) center and zoom parameters.
        'markers' => array(),

        // defines a single path of two or more connected points to overlay on the image at specified locations. This parameter takes
        // a string of point definitions separated by the pipe character (|). You may supply additional paths by adding additional path parameters.
        // Note that if you supply a path for a map, you do not need to specify the (normally required) center and zoom parameters.
        'path' => NULL,

        // specifies one or more locations that should remain visible on the map, though no markers or other indicators will be displayed.
        // Use this parameter to ensure that certain features or map locations are shown on the static map.
        'visible' => NULL,

        // defines a custom style to alter the presentation of a specific feature (road, park, etc.) of the map.
        // This parameter takes feature and element arguments identifying the features to select and a set of style operations
        // to apply to that selection. You may supply multiple styles by adding additional style parameters.
        'style' => array(),
    );

    /**
     * Reporting Parameters
     *
     * @var array
     */
    protected $reportingParameters = array(
        // specifies whether the application requesting the static map is using a sensor to determine the user's location.
        // This parameter is required for all static map requests.
        'sensor' => FALSE,
    );

    /**
     * @param string $location|NULL
     */
    public function __construct($location = NULL)
    {
        if (count(func_get_args()) > 1) {
            throw new InvalidStateException('More parameters defined in the class should be only one or none.');
        }

        if (!is_null($location)) {
            $this->setLocation($location);
        }

        $this->url = new Url(self::URL);
    }

    /**
     * set location
     *
     * @param mixed $location
     * @return this
     */
    public function setLocation($location)
    {
        $this->locationParameters['center'] = $location;

        return $this;
    }

    /**
     * set zoom
     *
     * @param int $zoom
     * @return this
     */
    public function setZoom($zoom)
    {
        $this->locationParameters['zoom'] = (int) $zoom;

        return $this;
    }

    /**
     * set size
     *
     * @param string $value
     * @return this
     */
    public function setSize($value)
    {
        $this->mapParameters['size'] = $value;

        return $this;
    }

    /**
     * set marker
     *
     * @param string $value
     * @return this
     */
    public function setMarker($value)
    {
        $this->featureParameters['markers'][] = $value;

        return $this;
    }

    /**
     * set style
     *
     * @param string $value
     * @return this
     */
    public function setStyle($value)
    {
        $this->featureParameters['style'][] = $value;

        return $this;
    }

    /**
     * get resource image
     *
     * @return resource
     */
    public function getResource()
    {
        if (is_null($this->locationParameters['center'])) {
            throw new GoogleMapsImageException("location was found.");
        }

        $url = (string) $this->generateUrl();
        $i = @getimagesize($url);
        if (!is_array($i)) {
            throw new GoogleMapsImageException("response from the server is not the type of image.");
        }

        switch ($i['mime']) {
            case 'image/png':
                $resource = imagecreatefrompng($url);
            break;
            case 'image/gif':
                $resource = imagecreatefromgif($url);
            break;
            case 'image/jpeg':
                $resource = imagecreatefromjpeg($url);
            break;
            default:
                throw new GoogleMapsImageException('this '.$i['mine'].' format is not supported for processing.');
            break;
        }

        if (!is_resource($resource)) {
            throw new GoogleMapsImageException("image was obtained from the server.");
        }

        return $resource;
    }

    /**
     * get image
     *
     * @return \Nette\Image
     */
    public function getImage()
    {
        return new Image($this->getResource());
    }

    /**
     * get url image
     *
     * @return \Nette\Http\Url
     */
    public function getUrl()
    {
        return $this->generateUrl();
    }

    /**
     * Saves image to the file.
     *
     * @param string $filename
     * @param  int  quality 0..100 (for JPEG and PNG)
     * @param  int  optional image type
     * @return bool TRUE on success or FALSE on failure.
     */
    public function save($file = NULL, $quality = NULL, $type = NULL)
    {
        $image = $this->getImage();
        $image->save($file, $quality, $type);
    }

   /**
    * Outputs image to browser.
    *
    * @param  int  image type
    * @param  int  quality 0..100 (for JPEG and PNG)
    * @return bool TRUE on success or FALSE on failure.
    */
    public function send($type = Image::JPEG, $quality = NULL)
    {
        $image = $this->getImage();
        $image->send($type, $quality);
    }

/****************
 *
 * Private functions
 *
 */

    private function generateUrl()
    {
        $values = array();
        foreach ($this->reflection->getProperties() as $property) {
            if (!preg_match('/(\w)Parameters/', $property->name)) {
                continue;
            }

            $values += $this->{$property->name};
        }

        $parameters = array();
        foreach ($values as $key => $value) {
            if ($value === TRUE) {
                $value = 'true';
            } elseif ($value === FALSE) {
                $value = 'false';
            } elseif (is_array($value) && count($value) > 0) {
                $value = implode("&".$key, $value);
            } elseif ((is_null($value) && $value == '') || (is_array($value) && count($value) == 0)) {
                continue;
            }

            $parameters[$key] = $value;
        }

        return $this->url->setQuery($parameters);
    }

}

class GoogleMapsImageException extends \Exception {}
