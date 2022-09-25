<?php

class TreeCore
{
	#region DECLARATIONS
	public $VisitCardWidth  = 100;
	public $VisitCardHeight = 60;
	public $VisitCardStrokeWidth = 1;
	public $VisitCardStrokeColor = 'rgb(200, 200, 200)';

	public $LineHorLength      = 50;
	public $LineVerLength      = 50;
	public $LineStrokeWidth = 1;
	public $LineColor		= 'rgb(200, 200, 200)';
	#endregion

	#region PUBLIC
	public function draw_visit_card($start_x, $start_y)
	{
		return "<rect x='{$start_x}' y='{$start_y}' rx='10' ry='10' width='{$this->VisitCardWidth}' height='{$this->VisitCardHeight}' fill='url(#grad1)'
				style='stroke:{$this->VisitCardStrokeColor};stroke-width:{$this->VisitCardStrokeWidth};opacity:1' />";
	}
	public function draw_straight_line($start_x, $start_y, $end_x, $end_y)
	{
		return "<line x1='{$start_x}' y1='{$start_y}' x2='{$end_x}' y2='{$end_y}' style='stroke:{$this->LineColor};stroke-width:{$this->LineStrokeWidth}'/>";

		// return '
		// 		<path d="M 50 60 L 50 50 Q 50 30 70 30 L 150 30" stroke="white" stroke-width="1" fill="none" />';
	}

	//control drawings
	public function draw_dot($start_x, $start_y)
	{
		return "<circle cx='{$start_x}' cy='{$start_y}' r='2' style='stroke:{$this->LineColor}' />";
	}
	public function draw_frame($start_x, $start_y, $width, $length)
	{
		return "<rect x='{$start_x}' y='{$start_y}' width='{$width}' height='{$length}' 
				style='stroke:rgb(200, 100, 200);stroke-width:{$this->VisitCardStrokeWidth}; opacity:0.4' />";
	}
	#endregion
	#region PRIVATE

	#endregion
}
?>