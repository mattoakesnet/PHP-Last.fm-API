<?php

class lastfmApiAlbum extends lastfmApiBase {
	public $info;
	public $tags;
	
	private $apiKey;
	private $artist;
	private $album;
	private $mbid;
	private $auth;
	
	function __construct($apiKey, $artist = '', $album = '', $mbid = '') {
		$this->apiKey = $apiKey;
		$this->artist = $artist;
		$this->album = $album;
		$this->mbid = $mbid;
	}
	
	public function getInfo() {
		$vars = array(
			'method' => 'album.getinfo',
			'api_key' => $this->apiKey,
			'album' => $this->album,
			'artist' => $this->artist,
			'mbid' => $this->mbid
		);
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			$this->info['name'] = (string) $call->album[0]->name;
			$this->info['artist'] = (string) $call->album[0]->artist;
			$this->info['lastfmid'] = (string) $call->album[0]->id;
			$this->info['mbid'] = (string) $call->album[0]->mbid;
			$this->info['url'] = (string) $call->album[0]->url;
			$this->info['releasedate'] = strtotime(trim((string) $call->album[0]->releasedate));
			$this->info['image']['small'] = (string) $call->album[0]->image[0];
			$this->info['image']['medium'] = (string) $call->album[0]->image[1];
			$this->info['image']['large'] = (string) $call->album[0]->image[2];
			$this->info['listeners'] = (string) $call->album[0]->listeners;
			$this->info['playcount'] = (string) $call->album[0]->playcount;
			$i = 0;
			foreach ( $call->album[0]->toptags->tag as $tags ) {
				$this->info['toptags'][$i]['name'] = (string) $tags->name;
				$this->info['toptags'][$i]['url'] = (string) $tags->url;
				$i++;
			}
			
			return $this->info;
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
	
	public function getTags($sessionKey, $secret) {
		$vars = array(
			'method' => 'album.gettags',
			'api_key' => $this->apiKey,
			'sk' => $sessionKey,
			'album' => $this->album,
			'artist' => $this->artist
		);
		$sig = $this->apiSig($secret, $vars);
		$vars['api_sig'] = $sig;
		
		$call = $this->apiGetCall($vars);
		
		if ( $call['status'] == 'ok' ) {
			if ( count($call->tags->tag) > 0 ) {
				$i = 0;
				foreach ( $call->tags[0]->tag as $tag ) {
					$this->tags[$i]['name'] = (string) $tag->name;
					$this->tags[$i]['url'] = (string) $tag->url;
					$i++;
				}
				
				return $this->tags;
			}
			else {
				// No tagsare found
				$this->error['code'] = 90;
				$this->error['desc'] = 'Artist has no tags from this user';
				return FALSE;
			}
		}
		elseif ( $call['status'] == 'failed' ) {
			// Fail with error code
			$this->error['code'] = $call->error['code'];
			$this->error['desc'] = $call->error;
			return FALSE;
		}
		else {
			//Hard failure
			$this->error['code'] = 0;
			$this->error['desc'] = 'Unknown error';
			return FALSE;
		}
	}
}

?>