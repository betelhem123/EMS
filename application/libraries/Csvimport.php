<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Csvimport {
    private $handle = "";
    private $filepath = FALSE;
    private $column_headers = FALSE;
    private $initial_line = 0;
    private $delimiter = ",";
    private $detect_line_endings = FALSE;
 
    public function get_array($filepath=FALSE, $column_headers=FALSE, $detect_line_endings=FALSE, $initial_line=FALSE, $delimiter=FALSE)
    {
        // Raise memory limit (for big files)
        ini_set('memory_limit', '20M');
        
        // File path
        if(! $filepath)
        {
            $filepath = $this->_get_filepath();    
        }
        else
        {   
            // If filepath provided, set it
            $this->_set_filepath($filepath);
        }
        // If file doesn't exists, return false
        if(! file_exists($filepath))
        {
            return FALSE;            
        }
        // auto detect row endings
        if(! $detect_line_endings)
        {
            $detect_line_endings = $this->_get_detect_line_endings();    
        }
        else
        {   
            // If detect_line_endings provided, set it
            $this->_set_detect_line_endings($detect_line_endings);
        }
        // If true, auto detect row endings
        if($detect_line_endings) 
        {
            ini_set("auto_detect_line_endings", TRUE);
        }
        // Parse from this line on
        if(! $initial_line)
        {
            $initial_line = $this->_get_initial_line();    
        }
        else
        {
            $this->_set_initial_line($initial_line);
        }
        // Delimiter
        if(! $delimiter)
        {
            $delimiter = $this->_get_delimiter();    
        }
        else
        {   
            // If delimiter provided, set it
            $this->_set_delimiter($delimiter);
        }
        // Column headers
        if(! $column_headers)
        {
            $column_headers = $this->_get_column_headers();    
        }
        else
        {
            // If column headers provided, set them
            $this->_set_column_headers($column_headers);
        }
        // Open the CSV for reading
        $this->_get_handle();
        
        $row = 0;
        while (($data = fgetcsv($this->handle, 0, $this->delimiter)) !== FALSE) 
        {     
            if ($data[0] != NULL) 
            {
                if($row < $this->initial_line)
                {
                    $row++;
                    continue;
                }
                // If first row, parse for column_headers
                if($row == $this->initial_line)
                {
                    // If column_headers already provided, use them
                    if($this->column_headers)
                    {
                        foreach ($this->column_headers as $key => $value)
                        {
                            $column_headers[$key] = trim($value);
                        }
                    }
                    else // Parse first row for column_headers to use
                    {
                        foreach ($data as $key => $value)
                        {
                              $column_headers[$key] = trim($value);
                        }                
                    }          
                }
                else
                {
                    $new_row = $row - $this->initial_line - 1; // needed so that the returned array starts at 0 instead of 1
                    foreach($column_headers as $key => $value) // assumes there are as many columns as their are title columns
                    {
                    $result[$new_row][$value] = utf8_encode(trim($data[$key]));
                    }
                }
            
                unset($data);
            
                $row++;
            }
        }
 
        $this->_close_csv();
        return $result;
    }
    
   
    private function _set_detect_line_endings($detect_line_endings)
    {
        $this->detect_line_endings = $detect_line_endings;
    }
  
    public function detect_line_endings($detect_line_endings)
    {
        $this->_set_detect_line_endings($detect_line_endings);
        return $this;
    }
  
    private function _get_detect_line_endings()
    {
        return $this->detect_line_endings;
    }
 
    private function _set_initial_line($initial_line)
    {
       return $this->initial_line = $initial_line;
    }
  
    public function initial_line($initial_line)
    {
        $this->_set_initial_line($initial_line);
        return $this;
    }
  
    private function _get_initial_line()
    {
        return $this->initial_line;
    }

    private function _set_delimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
      public function delimiter($delimiter)
    {
        $this->_set_delimiter($delimiter);
        return $this;
    }

    private function _get_delimiter()
    {
        return $this->delimiter;
    }

    private function _set_filepath($filepath)
    {
        $this->filepath = $filepath;
    }
 
    public function filepath($filepath)
    {
        $this->_set_filepath($filepath);
        return $this;
    }

    private function _get_filepath()
    {
        return $this->filepath;
    }
     private function _set_column_headers($column_headers='')
    {
        if(is_array($column_headers) && !empty($column_headers))
        {
            $this->column_headers = $column_headers;
        }
    }
    public function column_headers($column_headers)
    {
        $this->_set_column_headers($column_headers);
        return $this;
    }
      private function _get_column_headers()
    {
        return $this->column_headers;
    }
     private function _get_handle()
    {
        $this->handle = fopen($this->filepath, "r");
    }

    private function _close_csv()
    {
        fclose($this->handle);
    }    
}