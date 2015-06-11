<?php

class Validator
{
    protected $_requiredInput;
    protected $_inputType;
    protected $_errors;
    protected $_submittedInput;
    protected $_missingInput;
    protected $_filterArgs;
    protected $_filteredData;
    protected $_booleans;

    public function __construct($requiredInput = array(), $inputType = 'post')
    {
        if (!is_array($requiredInput)) {
            throw new Exception('Validator constructor requires only array for first argument.');
        }
        $this->_requiredInput = $requiredInput;
        $this->setInputType($inputType);
        if ($this->_requiredInput) {
            $this->checkRequiredInput();
        }
        $this->_filterArgs = array();
        $this->_booleans = array();
        $this->_errors = array();
    }

    public function isInt($filterFieldName, $min = null, $max = null)
    {
        if (!is_array($filterFieldName)) {
            $filterFieldName = array(trim($filterFieldName));
        }
        foreach ($filterFieldName as $value) {
            $this->checkExistenceFilter($value);
            $this->_filterArgs[$value] = array('filter' => FILTER_VALIDATE_INT);
            if (is_int($min)) {
                $this->_filterArgs[$value]['option']['min_range'] = $min;
            }
            if (is_int($max)) {
                $this->_filterArgs[$value]['option']['max_range'] = $max;
            }
        }
    }

    public function noFilter($filterFieldName, $isArray = false, $encodeAmp = false)
    {
        if (!is_array($filterFieldName)) {
            $filterFieldName = array(trim($filterFieldName));
        }
        foreach ($filterFieldName as $value) {
            $this->checkExistenceFilter($value);
            $this->_filterArgs[$value]['filter'] = FILTER_UNSAFE_RAW;
            $this->_filterArgs[$value]['flags'] = 0;
            if ($isArray) {
                $this->_filterArgs[$value]['flags'] |= FILTER_REQUIRE_ARRAY;
            }
            if ($encodeAmp) {
                $this->_filterArgs[$value]['flags'] |= FILTER_FLAG_ENCODE_AMP;
            }
        }
    }

    public function validateInput()
    {
        $notFiltered = array();

        $filterField = array_keys($this->_filterArgs);

        $notFiltered = array_diff($this->_requiredInput, $filterField);

        if ($notFiltered) {
            throw new Exception("No filter has been set for the following 
                required fields '" . implode(',', $notFiltered) . "'");
        }

        $this->_filteredData = filter_input_array($this->_inputType, $this->_filterArgs);

        foreach ($this->_filteredData as $key => $value) {
            // if (in_array($key, $this->_booleans) || in_array($key, $this->_missingInput) 
            //     || in_array($key, $this->_requiredInput)) {
            //     continue;
            // } else 
            if ($value === false) {
                array_push($this->_errors, "Invalid data type supplied for '" . ucfirst($key) . "'");
            }
        }

        return $this->_filteredData;
    }

    public function getMissingInput() 
    {
        return $this->_missingInput;
    }

    public function getError()
    {
        return $this->_errors;
    }

    protected function setInputType($inputType)
    {
        $inputType = strtolower($inputType);
        if ($inputType = 'post') {
            $this->_inputType = INPUT_POST;
            $this->_submittedInput = $_POST;
        } else if ($inputType = 'get') {
            $this->_inputType = INPUT_GET;
            $this->_submittedInput = $_GET;
        } else {
            throw new Exception('Invalid input type. Only allow POST and GET input type.');
        }
    }

    protected function checkRequiredInput()
    {
        $submitted = array();
        foreach ($this->_submittedInput as $key => $value) {
            $value = is_array($value) ? $value : trim($value);
            if (!empty($value)) {
                array_push($submitted, $key);
            }
        }
        $this->_missingInput = array_diff($this->_requiredInput, $submitted);
    }

    protected function checkExistenceFilter($filterFieldName)
    {
        if (isset($this->_filterArgs[$filterFieldName])) {
            throw new Exception("Filter for field name $filterFieldName already set.");
        }
    }
}
?>
