<?php

/**
 * Rule
 *
 * @package Less
 * @subpackage tree
 */
class Less_Tree_Rule extends Less_Tree{

	public $name;
	public $value;
	public $important;
	public $merge;
	public $index;
	public $inline;
	public $variable;
	public $currentFileInfo;
	public $type = 'Rule';

	/**
	 * @param string $important
	 */
	public function __construct($name, $value = null, $important = null, $merge = null, $index = null, $currentFileInfo = null,  $inline = false){
		$this->name = $name;
		$this->value = ($value instanceof Less_Tree_Value) ? $value : new Less_Tree_Value(array($value));
		$this->important = $important ? ' ' . trim($important) : '';
		$this->merge = $merge;
		$this->index = $index;
		$this->currentFileInfo = $currentFileInfo;
		$this->inline = $inline;
		$this->variable = ( is_string($name) && $name[0] === '@');
	}

	function accept($visitor) {
		$this->value = $visitor->visitObj( $this->value );
	}

    /**
     * @see Less_Tree::genCSS
     */
	function genCSS( $output ){

		$output->add( $this->name . Less_Environment::$_outputMap[': '], $this->currentFileInfo, $this->index);
		try{
			$this->value->genCSS( $output);

		}catch( Exception $e ){
			$e->index = $this->index;
			$e->filename = $this->currentFileInfo['filename'];
			throw $e;
		}
		$output->add( $this->important . (($this->inline || (Less_Environment::$lastRule && Less_Parser::$options['compress'])) ? "" : ";"), $this->currentFileInfo, $this->index);
	}

	public function compile ($env){

		$name = $this->name;
		if( is_array($name) ){
			// expand 'primitive' name directly to get
			// things faster (~10% for benchmark.less):
			if( count($name) === 1 && $name[0] instanceof Less_Tree_Keyword ){
				$name = $name[0]->value;
			}else{
				$name = $this->CompileName($env,$name);
			}
		}

		$strictMathBypass = Less_Parser::$options['strictMath'];
		if( $name === "font" && !Less_Parser::$options['strictMath'] ){
			Less_Parser::$options['strictMath'] = true;
		}

		// missing try ... catch
		if( Less_Environment::$mixin_stack ){
			$return = new Less_Tree_Rule($name, $this->value->compile($env), $this->important, $this->merge, $this->index, $this->currentFileInfo, $this->inline);
		}else{
			$this->name = $name;
			$this->value = $this->value->compile($env);
			$return = $this;
		}

		Less_Parser::$options['strictMath'] = $strictMathBypass;

		return $return;
	}

	function CompileName( $env, $name ){
		$output = new Less_Output();
		foreach($name as $n){
			$n->compile($env)->genCSS($output);
		}
		return $output->toString();
	}

	function makeImportant(){
		return new Less_Tree_Rule($this->name, $this->value, '!important', $this->merge, $this->index, $this->currentFileInfo, $this->inline);
	}

}
