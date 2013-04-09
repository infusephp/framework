<?php
/*
 * @package nFuse
 * @author Jared King <j@jaredtking.com>
 * @link http://jaredtking.com
 * @version 1.0
 * @copyright 2013 Jared King
 * @license MIT
	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
	associated documentation files (the "Software"), to deal in the Software without restriction,
	including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
	and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
	subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all copies or
	substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
	LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
	SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class Page extends Model
{	
	/**
	 * Constructor
	 *
	 * @param int $id ID
	 * @param string $theme theme
	 *
	 * @param boolean $loadInfo loads the page info if true
	*/
	function __construct( $id = -1, $theme = null )
	{
		$this->infoLoaded = false;
		
		if( is_numeric( $id ) )
			$this->id = $id;
		else if( $this->dbSupport && !empty( $id ) )
		{
			// check for slug
			// check for a theme specific page first
			$id = Database::select(
				'Page',
				'id',
				array(
					'where' => array(
						'nickname' => $id_,
						'theme' => Globals::$theme ),
					'single' => true ) );
			
			// no specific themed pages found, search for 'All' theme
			if( empty( $theme ) && Database::numrows() != 1 )
				$id = Database::select(
					'Page',
					'id',
					array(
						'where' => array(
							'nickname' => $id_,
							'theme' => 'All' ),
						'single' => true ) );
			
			if( Database::numrows() == 1 )
				$this->id = $id;
			else
				$this->id = -1;
		}
		else
			$this->id = -1;
	}
	
	/**
	* Gets the page content
	*
	* @return string content
	*/
	function content()
	{
		return $this->getProperty( 'content' );
	}
	
	/**
	 * Gets the page description
	 *
	 * @param string $description description
	 *
	 * @return string description
	 */
	function description( $description = null )
	{
		if( $description )
			$this->cacheProperty( 'description', $description );
		return $this->getProperty( 'description' );
	}

	/**
	 * Gets the page keywords
	 *
	 * @param string $keywords keywords
	 *
	 * @return string keywords
	 */
	function keywords( $keywords = null )
	{
		if( $keywords )
			$this->cacheProperty( 'keywords', $keywords );
		return $this->getProperty( 'keywords' );
	}
	
	/**
	 * Gets the page title
	 *
	 * @param string $title title
	 *
	 * @return string title
	 */
	function title( $title = null )
	{
		if( $title )
			$this->cacheProperty( 'title', $title );
		return $this->getProperty( 'title' );
	}
	
	/**
	 * Gets the page robot string
	 *
	 * @param string 
	 *
	 * @return string robots
	 */
	function robots( $robots = null )
	{
		if( $robots )
			$this->cacheProperty( 'robots', $robots );
		$robots = $this->getProperty( 'robots' );
		return ( empty( $robots ) ) ? 'index,follow' : $robots;
	}
	
	/**
	 * Gets the timestamp for which the page was
	 *
	 * @return int timestamp
	 */
	function timestamp()
	{
		return $this->getProperty( 'timestamp' );
	}
	
	/**
	 * Checks if the page exists
	 *
	 * @return boolean true if the page exists
	 */
	function exists()
	{
		if( $this->id > 0 )
			return Database::select(
				'Page',
				'count(*)',
				array(
					'where' => array(
						'id' => $this->id ),
					'single' => true ) ) == 1;
		else
			return true;
	}
	
	/**
	 * Gets the slug for the page
	 *
	 * @return string slug
	 */
	function slug()
	{
		$slug = $this->getProperty( 'slug' );
		if( $slug )
			return $slug;
		else
			return $this->id;
	}
	
	/*
	 * Gets the page URL
	 *
	 * @return string URL
	 */
	function url()
	{
		if( $this->id > 0 )
		{
			if( is_numeric( $page ) && $module == 'Page' && $this->dbSupport )
			{
				// lookup slug
			}
	
			return urlPrefix() . Config::value( 'site', 'host-name' ) . '/pages/' . $this->slug();
		}
		else
			return curPageURL();
	}
	
	/**
	 * Displays the page from the database
	 *
	 * @return null
	 */
	function display()
	{
		if( $this->id > 0 )
			Globals::$smarty->display("db:{$this->id}");
	}
}