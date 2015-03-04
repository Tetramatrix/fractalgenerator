<?
/* * *************************************************************
 * Copyright notice
 *
 * (c) 2014 Chi Hoang (info@chihoang.de)
 *  All rights reserved
 *
 * **************************************************************/
class fractal
{
    var $startRe;
    var $startIm;    
    var $endeRe;
    var $endeIm;
    var $zRe;
    var $zIm;
    var $stepsRe;
    var $stepsIm;

    var $iterations;
    var $size_x;
    var $size_y;
    var $pal_startcolor;
    var $pal_endcolor;
     
    var $pixelblock = array();
    var $iMatrix = array();
    var $dim = 16;        
    var $len = 25;
    var $height = 10;
    var $method = 0; //0=mandelbrot,1=julia

    var $preset = array ( '0'=> array(),
                          '1'=> array(-2.5,-1.5,1.5,1.5),
                          '2'=> array(-1.3163,-0.4173,-1.0836,-0.1847),
                          '3'=> array(-1.23854466281,-0.390617339835,-1.26586983793,-0.3760361853),
			  '4'=> array(-1.72382375,0.0581391,-1.1572953125,-0.16465005),
			  '5'=> array(-1.36972842709,-0.0705535683853,-1.36902783803,-0.0708322070722)
                          );
                          
    var $color = array (    '0' => array ( 0x2020FF,0xFFFFFF ),
                            '1' => array ( 0xff8a00,0x0000ff ),
                            '2' => array ( 0xFF2020,0xfff000 ),
                            '3' => array ( 0xFFFF00,0xff0000 ),
                            '4' => array ( 0x20FF20,0xff0000 ),                            
                            '5' => array ( 0x833d1a,0xff0000 ),
                            '6' => array ( 0x000000,0xFFFFFF ),
                        );
                        
    function fractal()
    {
        // Komplexe Zahl Z, bestehend aus Real- und Imaginärteil (defaults)
        // (Ausgangspunkt/Endpunkt der Berechnungen festlegen)
        $this->startRe = (double)-2.5;
        $this->startIm = (double)-1.5;
        
        $this->endeRe = (double)1.5;
        $this->endeIm = (double)1.5;
        
        // Mit complex->z wird gerechnet
        $this->zRe = (double)0;
        $this->zIm = (double)0;
        
        // Eigenschaften festlegen (defaults)
        $this->iterations = (integer)150;
       
	// Farbverlauf: Startfarbe & Endfarbe
        $this->pal_startcolor = 0x2020FF;
        $this->pal_endcolor = 0xFF2020;
    }

    // Mandelbrot Fraktal berechnen
    function render()
    {    
        $tStart = microtime(true);
        
        $this->size_x = $this->dim*$this->len;
        $this->size_y = $this->height*$this->len; 
        
        // Punkte des Bildes berechnen
        for ($y = 0; $y < $this->size_y; $y++)
	{
            for ($x = 0; $x < $this->size_x; $x++)
	    {     
                $this->iMatrix[$y][$x] = array('x'=> $x,'y'=> $y);
            }
        }
        
        foreach ($this->iMatrix as $k => $v)
	{
            for ($i=0;$i<$this->dim;$i++)
	    {
                for ($j=$i*$this->len;$j<($i+1)*$this->len;$j++)
		{
                    $this->pixelblock[$k][$i][]=$v[$j];
                }            
            }
        } 
        unset($this->iMatrix);
        
        // Schrittweite der komplexen Punkte berechnen
        $this->stepsRe = (double)((($this->startRe * -1) + ($this->endeRe)) / ($this->size_x-1));
        $this->stepsIm = (double)((($this->startIm * -1) + ($this->endeIm)) / ($this->size_y-1));
        
        // Startpunkt als ersten Berechnungspunkt festlegen
        $this->zRe = $this->startRe;
        $this->zIm = $this->startIm;        
    
        // Startwert der X-Achse (Realteil der komplexen Zahl) speichern
        $re_start = $this->zRe;
        
        // Bild erzeugen
        $image = imagecreatetruecolor($this->size_x, $this->size_y+$this->len);
        if (!$image)
	{
            $image = imagecreate ($this->size_x, $this->size_y+$this->len);        
        }
        
        // Palette erzeugen       
        $palette = $this->image_createpalette( $image, $this->pal_startcolor, $this->pal_endcolor );
        
        $skip = 0;
        
        // Punkte des Bildes berechnen
        for( $row = 0; $row < $this->height; $row++ )
        {          
            for( $d = 0; $d < $this->dim; $d++ )
            {   
                $this->zRe = $this->startRe;  
                $this->zIm = $this->startIm+$this->stepsIm*($row*$this->len);
                $cgeTop=$cgeLeft=$cgeRight=$cgeBottom=0;    
                
                $this->zRe = (double)($re_start+$this->stepsRe*$d*$this->len);
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if($steps_done < $this->iterations)
		{
                    //$cgeTop++;
                    //$cgeLeft++;
                    $cgeTop=$cgeLeft=$steps_done;
                }
                $this->zRe += $this->stepsRe*$this->height;
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if($steps_done < $this->iterations)
		{
                    //$cgeTop++;
                    $cgeTop=$steps_done; 
                }                
                $this->zRe += $this->stepsRe*$this->height;
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if($steps_done < $this->iterations)
		{
                    //$cgeTop++;
                    //$cgeRight++;
                    $cgeTop=$cgeRight=$steps_done;
                }                
               
                $this->zRe = (double)($re_start+$this->stepsRe*$d*$this->len);
                $this->zIm = (double)$this->startIm+$this->stepsIm*($row*$this->len)+$this->stepsIm*10; 
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if( $steps_done < $this->iterations )
		{
                    //$cgeLeft++;
                    $cgeLeft=$steps_done;
                }
                
                $this->zRe += $this->stepsRe*$this->len;
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if( $steps_done < $this->iterations )
		{
                    //$cgeRight++;
                    $cgeRight=$steps_done;
                }
                
                $this->zRe = (double)($re_start+$this->stepsRe*$d*$this->len);
                $this->zIm = (double)$this->startIm+$this->stepsIm*($row*$this->len)+$this->stepsIm*$this->len; 
                $steps_done = $this->complex($this->zRe,$this->zIm);                
                if($steps_done < $this->iterations)
		{
                    //$cgeBottom++;
                    //$cgeLeft++;
                    $cgeBottom=$cgeLeft=$steps_done;
                }
                $this->zRe += $this->stepsRe*$this->height;
                $steps_done = $this->complex($this->zRe,$this->zIm);                
                if($steps_done < $this->iterations)
		{
                    //$cgeBottom++;
                    $cgeBottom=$steps_done; 
                }
                $this->zRe += $this->stepsRe*$this->height;
                $steps_done = $this->complex($this->zRe,$this->zIm);
                if($steps_done < $this->iterations)
		{
                    //$cgeBottom++;
                    //$cgeRight++;
                    $cgeBottom=$cgeRight=$steps_done;                    
                }
               
                $b1 = array();
		for ($i=$row*$this->len,$e=($row+1)*$this->len+1;$i<$e;$i++)
                {
                    for ($j=0;$j<$this->len;$j++)
		    {
                        $b1[$i][] = $this->pixelblock[$i][$d][$j];
                    }
                }
                
                $this->zRe = $this->startRe; 
                $this->zIm = $this->startIm+$this->stepsIm*($row*$this->len);  
                
                if ($cgeTop == $cgeBottom && $cgeRight == $cgeLeft) 
                {
                    $skip++;
                    foreach ($b1 as $k => $v)
                    {
                        foreach ($v as $k1 => $v1)
                        {
                           ImageSetPixel($image, $v1['x'], $v1['y'], $palette[$steps_done]);
                        }
                    }
                } else 
		{            
                    foreach ($b1 as $k => $v)
		    {    
                        $this->zRe = (double)($re_start+$this->stepsRe*$d*$this->len);

                        foreach ($v as $k1 => $v1)
			{
                            $steps_done = $this->complex($this->zRe,$this->zIm);                                       
                            
                            // Wurde die Iteration abgebrochen, ist der Punkt ausserhalb der Mandelbrot-Menge
                            if ( $steps_done < $this->iterations )
                            {
                                ImageSetPixel($image,$v1['x'],$v1['y'], $palette[$steps_done] );
                            } else
			    {                                
                                ImageSetPixel($image,$v1['x'],$v1['y'], 0x000000 );
                            }
                            $this->zRe += $this->stepsRe;
                        }                        
                        $this->zIm += $this->stepsIm;
                    }
                } 
            }
        }
        
        $runTime = (microtime(true)-$tStart);
        
        ob_start();        
        $string = "Time: ".round($runTime,2)." Sec. Skip: $skip Blocks"; 
        $orange = imagecolorallocate($image, 220, 210, 60);
        $px = (imagesx($image) - 7.5 * strlen($string)) / 2;
        imagestring($image, 3, $px, $this->size_y+3, $string, $orange);        
        ImagePNG($image);                
        $imagevariable = ob_get_contents();        
        ImageDestroy($image);
        ob_end_clean();
        
        return $imagevariable;
    }

    function complex($re,$im)
    {
	//bcscale(8);
        //Iteration durchfuehren
        
 	switch ($this->method) {
		case 0:
			// Mandelbrot
			$zisqr = $zrsqr = $iter = $zIm = $zRe = 0;
        		break;
        	case 1:
			// julia set
        		$iter = 0;
        		$zIm=$im;
        		$zRe=$re;
        		//$zrsqr = bcmul($zRe,$zRe);
        		//$zisqr = bcmul($zIm,$zIm);
        		$zrsqr = $zRe*$zRe;
			$zisqr = $zIm*$zIm;
			$re=-0.7;
        		$im=0.27015;
			break;
		case 2:
			// burning ship
			$iter = 0;
        		$zIm=$im;
        		$zRe=$re;
			
	}        

	switch ($this->method)
	{
	    case 2: {
	    
		while ($zrsqr+$zisqr < 4
		 && $iter++ < $this->iterations)
		{
		    $zIm=abs($zIm*$zRe);
		    $zIm += $zIm;
		    $zIm += $im;
		    $zRe = $zrsqr-$zisqr+$re;
		    $zrsqr = $zRe*$zRe;
		    $zisqr = $zIm*$zIm;
		    
		    //$zIm = bcmul($zIm,$zRe);
		    //$zIm = bcadd($zIm,$zIm);
		    //$zIm = bcadd($zIm,$im);
		    //$zRe = bcadd(bcsub($zrsqr,$zisqr),$re);
		    //$zrsqr = $zRe*$zRe;
		    //$zisqr = $zIm*$zIm;
		}
	    }
	    break;
	    default: {
		while ($zrsqr+$zisqr < 4
		&& $iter++ < $this->iterations)
	       {
		   $zIm *= $zRe;
		   $zIm += $zIm;
		   $zIm += $im;
		   $zRe = $zrsqr-$zisqr+$re;
		   $zrsqr = $zRe*$zRe;
		   $zisqr = $zIm*$zIm;
		   
		   //$zIm = bcmul($zIm,$zRe);
		   //$zIm = bcadd($zIm,$zIm);
		   //$zIm = bcadd($zIm,$im);
		   //$zRe = bcadd(bcsub($zrsqr,$zisqr),$re);
		   //$zrsqr = $zRe*$zRe;
		   //$zisqr = $zIm*$zIm;
	       }
	    }
	}
        return $iter;
    }

    // Verlaufs-Palette erzeugen
    function image_createpalette( &$image, $start_color, $end_color )
    {
        $palette = array();   
        
        // Die Aditionswerte ermitteln
        $add_r = ceil( ((($end_color & 0xFF0000) >> 16) - (($start_color & 0xFF0000) >> 16)) / $this->iterations);
        $add_g = ceil( ((($end_color & 0x00FF00) >> 8) - (($start_color & 0x00FF00) >> 8)) / $this->iterations);        
        $add_b = ceil( (($end_color & 0x0000FF) - ($start_color & 0x0000FF)) / $this->iterations);
        
        // RGB mit binärem UND errechnen
        $r = ($start_color & 0xFF0000) >> 16;
        $g = ($start_color & 0x00FF00) >> 8;
        $b = $start_color & 0x0000FF;
        
        for( $i = 0; $i < $this->iterations; $i++ )
        {
            $palette[] = ImageColorAllocate( $image, $r, $g, $b );
            $r += $add_r;
            $g += $add_g;
            $b += $add_b;
        }
        return $palette;
    }
}

?>
