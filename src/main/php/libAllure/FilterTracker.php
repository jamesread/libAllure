<?php
/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

if (defined(__FILE__)) { return; } else { define(__FILE__, true); }

class FilterTracker {                                                              
    private $filters = array();                                                    
    private $vars = array();                                                       
	private $options = array();
	private $hiddenVales = array();
                                                                                   
    private function add($name, $type, $label = null, $requestVar = null) {        
        if ($requestVar == null) {                                                 
            $requestVar = $name;                                                   
        }                                                                          
                                                                                   
        if ($label == null) {                                                      
            $label = ucwords($name);                                               
        }                                                                          
                                                                                   
        $this->types[$name] = $type;                                               
        $this->vars[$name] = $requestVar;                                          
        $this->labels[$name] = $label;                                             
        $this->options[$name] = null;                                                    
    }                                                                              

	public function setHiddenValue($name, $value, $requestVar = null) {
		$this->add($name, 'hidden', $name, $requestVar);
		$this->hiddenValues[$name] = $value;
	}
                                                                                   
    public function addInt($name, $label = null, $requestVar = null) {             
        $this->add($name, 'int', $label, $requestVar);                             
    }                                                                              
                                                                                   
    public function addBool($name, $label = null, $requestVar = null) {            
        $this->add($name, 'bool', $label, $requestVar = null);                     
    }                                                                              
                                                                                   
    public function addString($name, $label = null, $requestVar = null) {          
        $this->add($name, 'string', $label, $requestVar);                          
    }                                
	
	public function addSelect($name, $list, $nameField, $label = null, $requestVar = null) {
		$this->add($name, 'select', $label, $requestVar);

		foreach ($list as $key => $option) {
			$list[$key]['name'] = $option[$nameField];
		}

		$this->options[$name] = $list;	
	}
                                                                                   
    public function isUsed($name) {                                                
		if (!isset($this->vars[$name])) {
			return false;
		}

        if (isset($_REQUEST[$this->vars[$name]])) {                                
            if ($this->types[$name] != 'bool' && empty($_REQUEST[$this->vars[$name]])) {
                return false;                                                      
            }                                                                      
                                                                                   
            return true;                                                           
        }                                                                          
                                                                                   
        return false;                                                              
    }                                

    public function getAll() {                                                     
        $ret = array();                                                            
                                                                                   
        foreach ($this->vars as $name => $value) {
            $ret[] = array(                                                        
                'name' => $name,                                                  
				'varName' => $this->vars[$name],
                'isUsed' => $this->isUsed($name),                                  
                'type' => $this->types[$name],                                     
                'value' => $this->getValue($name),                                 
                'label' => $this->labels[$name],
				'options' => $this->options[$name]
            );                                                                     
        }                                                                          
                                                                                   
        return $ret;                                                               
    }                                                                           
                                                                                
    public function getValue($name) {                                           
        if ($this->isUsed($name)) {                                             
            if ($this->types[$name] == "bool") {                                
                return true;                                                    
            } else {                                                            
                return $_REQUEST[$this->vars[$name]];
            }                                                                   
        }                                                                       
                                                                                
        return false;                                                           
    }                                                                           
}    

?>
