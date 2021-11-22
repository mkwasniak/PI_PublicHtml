<?php

class TreePerson
{
#region DECLARATIONS
	public $SpaceWidth  = 50;
	public $SpaceHeight = 50;
	public $Partner     = NULL;
	public $StartX 		= 0;
	public $StartY 		= 0;
#endregion

#region PUBLIC
	public function __construct($firstRun, $StartX=10, $StartY=10, $TreeCore, $TreePerson = NULL, $Relation = 'P')
	{
		// global $CFG;
		// $this->CFG = $CFG;
		// $this->DB = new mysqli('localhost', 'genes', 'Genes!23', 'genes');
		$this->SpaceWidth  = $TreeCore->VisitCardWidth;
		$this->SpaceHeight = $TreeCore->VisitCardHeight;

		if (is_object($TreePerson)) {
			if ($Relation = 'P') {
				$this->StartX = $TreePerson->StartX;
				$this->StartY = $TreePerson->StartY - $TreeCore->LineVerLength - $TreeCore->VisitCardHeight;							
			}
		}
		else {
			$this->StartX = $StartX;
			$this->StartY = $StartY;
		}
		//todo
		//check for partner
		//add partner as a class object / require a flag in constructor to switch between first and deeper calls
		//adjust space
		if ($firstRun) {
			//move to the right from start to width of card and lenght of the line
			$this->Partner = new TreePerson(FALSE, $this->StartX+$TreeCore->VisitCardWidth+$TreeCore->LineHorLength, $this->StartY, $TreeCore);		
			$this->SpaceWidth = $this->SpaceWidth + $TreeCore->VisitCardWidth + $TreeCore->LineHorLength;	
		}
	}

	public function draw_person_visit_card($TreeCore)
	{
		$html = '';
		$html .= $TreeCore->draw_visit_card($this->StartX, $this->StartY);
		if (is_object($this->Partner)) {
			$html .= $this->Partner->draw_person_visit_card($TreeCore);		
			$html .= $this->draw_partner_line($TreeCore);	
		}
		return $html;
	}

	public function draw_person_space_frame($TreeCore) 
	{
		$html = '';

		$html .= $TreeCore->draw_frame($this->StartX, $this->StartY, $this->SpaceWidth, $this->SpaceHeight);
// var_dump($this->StartX, $this->StartY, $this->SpaceWidth, $this->SpaceHeight);
		return $html;
	}
#endregion


#region PRIVATE
	public function draw_partner_line($TreeCore)
	{
		//calculate X and Y
		$start_x = $this->StartX + $TreeCore->VisitCardWidth;
		$start_y = $this->StartY + ceil($TreeCore->VisitCardHeight / 2);
		$end_x 	 = $start_x + $TreeCore->LineHorLength;
		$end_y   = $start_y;

		$html = '';
		$html .= $TreeCore->draw_straight_line($start_x, $start_y, $end_x, $end_y);

		return $html;
	}
#endregion	
}

?>