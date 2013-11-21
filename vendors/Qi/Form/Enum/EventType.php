<?php
namespace Qi\Form\Enum;

/**
 * An enum of event constants.
 */
class EventType
{
    // Browser Events
    const LOAD = "onload";
    const UNLOAD = "onunload";

    // Keyboard Events
    const KEY_DOWN = "onkeydown";
    const KEY_UP = "onkeyup";
    const KEY_PRESS = "onkeypress";
    
    // Mouse Events
    const MOUSE_OVER = "onmouseover";
    const MOUSE_OUT = "onmouseout";
    const MOUSE_DOWN = "onmousedown";
    const MOUSE_UP = "onmouseup";
    const CLICK = "onclick";
    const DOUBLE_CLICK = "ondblclick";
    
    // Element Events
    const FOCUS = "onfocus";
    const BLUR = "onblur";

    // Button Events
    const PRESS = "onpress";
    const RELEASE = "onrelease";
    
    // Input Events
    const CHANGE = "onchange";
    
    // Form Events
    const SUBMIT = "onsubmit";
    const RESET = "onreset";
    
    private function __construct() {}
}
