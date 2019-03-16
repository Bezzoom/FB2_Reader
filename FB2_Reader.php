<?php

Class FB2_Reader {

	private $bookSource;
	private $infoSection;
	private $images = array();

	public function __construct( $inputFile ) {
		$this->bookSource = simplexml_load_file( $inputFile );
	}

	public function getAuthor( $format = 'short' ) {
		$this->getInfoSection();

		switch ( $format ) {
			case 'full':
				return $this->infoSection->author->{'first-name'}.' '.$this->infoSection->author->{'middle-name'}.' '.$this->infoSection->author->{'last-name'};
			case 'short':
			default:
				return $this->infoSection->author->{'last-name'}.' '.mb_substr( $this->infoSection->author->{'middle-name'}, 0, 1 ).'. '.mb_substr( $this->infoSection->author->{'first-name'}, 0, 1 ).'.';
		}
	}

	public function getTitle() {
		$this->getInfoSection();

		return $this->infoSection->{'book-title'}.'';
	}

	public function getAnnotation() {
		$this->getInfoSection();

		return $this->infoSection->annotation->p.'';
	}

	public function getGenre() {
		$this->getInfoSection();

		return $this->infoSection->genre.'';
	}

	public function getImages() {
		$imageinfo = array();

		foreach ( $this->bookSource->binary as $image ) {
			$data = base64_decode( $image );
			$imageinfo['type'] = $this->getImageType( $data );
			$imageinfo['resource'] = imagecreatefromstring( $data );

			array_push( $this->images, $imageinfo );
		}

		return $this->images;
	}

	public function saveImage( $image = NULL, $id = 0, $path = false ) {
		if ( is_null( $image ) ) {
			return false;
		}
		switch ( $image[$id]['type'] ) {
			case 'image/jpeg':
				if ( $path ) {
					imagejpeg( $image[$id]['resource'], $path.'.jpeg' );
					return $path.'.jpeg';
				} else {
					header('Content-Type: image/jpeg');
					imagejpeg( $image[$id]['resource'] );
				}
				break;
			
			case 'image/png':
				if ( $path ) {
					imagepng( $image[$id]['resource'], $path.'.png' );
					return $path.'.png';
				} else {
					header('Content-Type: image/png');
					imagepng( $image[$id]['resource'] );
				}
				break;
			
			default:
				return false;
				break;
		}

	}
	

	private function getImageType( $image ) {
		$info = getimagesizefromstring( $image );

		return $info['mime'];
	}

	private function getInfoSection() {
		$this->infoSection = $this->bookSource->description->{'title-info'};
	}
}

?>