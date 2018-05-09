<?php
/**
 * Class for logging in files
 *
 * @author Oscar Aviles <emeeseka01@gmail.com>
 */
class Logger {
    private $path;
    private $options;        
    private $level;
	private $data;
	private $start_time;
	private $last_time;
    
	/**
	 * 
	 * @param string $path Path where the log will be written (By default /tmp/UNIQID_DATE.log)
	 * @param type $level Level of logging captured
	 * @param boolean $options Options for constructing:	 
	 *      - printDate: Print the date (default:true)
	 *      - endLine: End of line char (default:PHP_EOL)
	 *      - print: Print the debug with a print_r as well (default:false)	 
	 * @return string Returns the path of the log
	 */
    function __construct($path='',$level=0,$options=[]) {
		//Default values
        if (empty($path)) {                    
            $path = '/tmp/'.uniqid().'_'.date('Ymd').'.log';            
        }                
        if (!isset($options['printDate'])) {
            $options['printDate'] = true;
        }        
        if (!isset($options['endLine'])) {
            $options['endLine'] = PHP_EOL;
        }        
            
		//Setting
        $this->setPath($path);          
        $this->setOptions($options);                            
        $this->setLevel($level);        
        
		//Profiling
		$this->start_time = microtime(true);
		$dat = getrusage();
		define('PHP_TUSAGE', microtime(true));
		define('PHP_RUSAGE', $dat["ru_utime.tv_sec"]*1e6+$dat["ru_utime.tv_usec"]);
		
		//Return
        return $path;
    }
    
	/**
	 * 
	 * @param type $txt The text to log
	 * @param type $level The level of the logging (default:1)
	 */
    function log($txt,$level=1) {
        $options = $this->getOptions();
        $aux = '';
        if ($level >= $this->getLevel()) {
            if (isset($options['printDate']) && $options['printDate'] === true) {
                $aux = date('Y-m-d H:i:s').' - ';
            }
            $aux .= $txt;
            if (isset($options['endLine'])) {
                $aux .= $options['endLine'];
            }
            file_put_contents($this->getPath(), $aux, FILE_APPEND);
            $this->_print($aux);
        }
    }
	
	public function _print($aux) {
		$options = $this->getOptions();
		if (isset($options['print'])) {
			echo '<pre>';print_r($aux);echo '</pre>';
		}
	}
	
	public function log_track($v=false) {
		//All
		if ($v === false) {
			$this->log(print_r($this->data,true));
		}
		//Specific
		else {
			if (isset($this->data[$v])) {
				$this->log(print_r($this->data[$v],true));
			} else {
				$this->log('Error trying log_track('.$v.'), the key was not tracked down.');
			}
		}
	}
	
	public function track($v=false) {
		if ($v === false) {
			$v = uniqid();
		}
		$this->data[$v]['memory'] = $this->formatBytes(memory_get_usage());
		$this->data[$v]['cpu'] = self::getCpuUsage(). ' %';
		$this->data[$v]['time'] = microtime(true) - $this->start_time;
		if (isset($this->last_time)) {
			if (floatval($this->data[$v]['time'] - $this->last_time) > 1) {
				$this->data[$v]['time_between'] = ($this->data[$v]['time'] - $this->last_time) . ' s';
			} else {
				$this->data[$v]['time_between'] = ($this->data[$v]['time'] - $this->last_time)*1000 . ' ms';
			}
		}
		$this->last_time = $this->data[$v]['time'];		
		if (floatval($this->data[$v]['time']) > 1) {			
			$this->data[$v]['time'] = $this->data[$v]['time'] . ' s';
		} else {
			$this->data[$v]['time'] = $this->data[$v]['time']*1000 . ' ms';
		}		
		
	}
		
	public function createIfNotExist($dir,$modo=02775) {
		if(!$dir) {
			return;	
		}		
        $ex = explode('/', $dir);
        unset($ex[count($ex)-1]);        
        $dir = implode('/',$ex);
	    $salida=false;
		if (!file_exists($dir)) {
			if (isset($_SERVER["WINDIR"])) $salida=@mkdir($dir);//windows
			else
			{
				$salida=@mkdir($dir, $modo, true);//no windows & recursive
				exec("chown -R www-data:www-data $dir");
			}
		}
		return $salida;
	}
	
	public function getCpuUsage() {
		$dat = getrusage();
		$dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - PHP_RUSAGE;
		$time = (microtime(true) - PHP_TUSAGE) * 1000000;

		// cpu per request
		if($time > 0) {
			$cpu = sprintf("%01.2f", ($dat["ru_utime.tv_usec"] / $time) * 100);
		} else {
			$cpu = '0.00';
		}

		return $cpu;
	}
	
	public function formatBytes($bytes, $precision = 2) {		
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 

		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow)); 

		return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 		
    
    function getPath() {
        return $this->path;
    }

    function getOptions() {
        return $this->options;
    }

    function getLevel() {
		return $this->level;
	}
	
    function setPath($path) {
        $this->createIfNotExist($path);
        $this->path = $path;
    }

    function setOptions($options) {
        $this->options = $options;
    }

    function setLevel($level) {
		$this->level = $level;
	}	         
}
