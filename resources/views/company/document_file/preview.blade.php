<style>
    .modal-dialog,
    html[data-bs-theme="dark"] .modal-dialog
    {
        max-width: 68%;
        height: 90vh;
        display: flex;
        align-items: center;
    }
    @media only screen and (max-width: 992px) { 
        .modal-dialog, html[data-bs-theme="dark"] .modal-dialog {
        max-width: 93%;
    }
        
    }
    
     #zoom-container {
         
            height: 79vh;
            overflow: hidden;
            position: relative;
        }

        #object {
            position: absolute;
            top: 0;
            left: 0;
            transform-origin: 0 0;
            will-change: transform;
        }

        #object-view img {
            display: block;
            max-width: none;
            user-drag: none;
            user-select: none;
            pointer-events: none;
        }
</style>

<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel1">Document  Preview</h5>
    <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
</div>
<div class="modal-body" id="zoom-container">
    <div class="img-box" id="object-view">
        @if($extension == 'pdf')
            <iframe src="{{ route('company/document/show', ['fileName' => $model->filename]) }}" frameborder="0" height="550px" width="100%"></iframe>
        @else
            <img id="zoomImage" class="w-100 active " src="{{ route('company/document/show', ['fileName' => $model->filename]) }}"  alt="Image Preview">
        @endif
    </div>
</div>


<script>

window.ELEMENT_ID = "object-view"; // constants
window.ALSO_DRAG_OUTSIDE = false;
window.MAX_ZOOM = 5;
window.MIN_ZOOM = 0.1;
window.SCROLL_FACTOR = 1.2;

// Global variables
window.element = null;
window.parent = null;
window.objLoc = null;
window.currentZoom = 1;
window.lastZoom = 1;
window.isDragging = false;
window.dragStart = { x: 0, y: 0 };
window.initialPinchDistance = null;
window.mouseMoved = false;
window.hasZoomed = false;
// Execute this function after your desired element has been loaded and
// after the above variables have been declared
function enablePanAndZoom() {
    element = document.getElementById(ELEMENT_ID);
    if (!element) {
        console.error(`Element with ID "${ELEMENT_ID}" not found. Pan and zoom cannot be enabled.`);
        return;
    }
    parent = ALSO_DRAG_OUTSIDE ? element.parentElement : element;
    initializeStyles();
    addNavigationListeners();
}

function initializeStyles() {
    element.style.transformOrigin = "0px 0px";
    element.style.width = "100%";
    if (element.parentElement) {
        element.parentElement.style.overflow = "hidden";
    }
    parent.style.cursor = "grab";
    resetTransform();
}

function addNavigationListeners() {
    parent.addEventListener('mousedown', handleMouse);
    parent.addEventListener('mousemove', handleMouse);
    window.addEventListener('mouseup', handleMouse);
    parent.addEventListener('touchstart', handleTouch);
    parent.addEventListener('touchmove', handleTouch);
    parent.addEventListener('touchend', handleTouch);
    parent.addEventListener('wheel', handleScroll);
}

// Transforms the movable object-view element
function resetTransform() {
    objLoc = { x: 0, y: 0 };
    currentZoom = 1;
    lastZoom = currentZoom;
    setTransform();
}

function setTransform() {
    const translate = "translate(" + objLoc.x + "px, " + objLoc.y + "px) ";
    const scale = "scale(" + currentZoom + ")";
    element.style.transform = translate + scale;
}

// Gets the relevant location from a mouse or touch event
function getEventLocation(e) {
    if (e.touches) {
        if (e.touches.length == 1) {
            return {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        } else {
            return [0, 1].map((i) => {
                return {
                    x: e.touches[i].clientX,
                    y: e.touches[i].clientY
                };
            });
        }
    } else if (e.clientX && e.clientY) {
        return {
            x: e.clientX,
            y: e.clientY
        };
    }
}

// Hub for mouse events
function handleMouse(e) {
    e.preventDefault();
    switch (e.type) {
        case "mousedown":
            mouseMoved = false;
            hasZoomed = false;
            onPointerDown(e);
            break;
        case "mouseup":
            onPointerUp(e);
            break;
        case "mousemove":
            if (isDragging) {
                onPointerMove(e);
            }
            break;
    }
}

// Hub for touch events
function handleTouch(e) {
    e.preventDefault();
    switch (e.type) {
        case "touchstart":
            if (e.touches.length == 1) {
                mouseMoved = false;
                hasZoomed = false;
            }
            onPointerDown(e);
            break;
        case "touchend":
            if (e.touches.length == 0) {
                onPointerUp(e);
            } else {
                onPointerDown(e);
            }
            break;
        case "touchmove":
            if (e.touches.length == 2) {
                isDragging = false;
                handlePinch(e);
            } else {
                onPointerMove(e);
            }
            break;
    }
}

// Hub for click events
function handleClick(e) {
    resetTransform(); // Replace with your desired click function.
}

// Drag functions
function onPointerDown(e) {
    parent.style.cursor = "grabbing";
    isDragging = true;
    const eLoc = getEventLocation(e);
    dragStart = {
        x: eLoc.x - objLoc.x,
        y: eLoc.y - objLoc.y
    };
}

function onPointerUp(e) {
    parent.style.cursor = "grab";
    isDragging = false;
    initialPinchDistance = null;
    if (!mouseMoved && !hasZoomed) {
        handleClick(e);
    }
    lastZoom = currentZoom;
}

function onPointerMove(e) {
    mouseMoved = true;
    parent.style.cursor = "grabbing";
    const eLoc = getEventLocation(e);
    objLoc.x = (eLoc.x - dragStart.x);
    objLoc.y = (eLoc.y - dragStart.y);
    setTransform();
}

// Zoom functions
function handleScroll(e) {
    if (isDragging) return;
    e.preventDefault();
    const delta = e.wheelDelta ? e.wheelDelta : -e.deltaY;
    const zoomFactor = delta > 0 ? SCROLL_FACTOR : 1 / SCROLL_FACTOR;
    setZoom(e, zoomFactor);
}

function handlePinch(e) {
    e.preventDefault();
    let touchLoc = getEventLocation(e);
    let currentDistance = euclideanDistance(touchLoc[0], touchLoc[1]);
    if (initialPinchDistance == null) {
        initialPinchDistance = currentDistance;
    } else if (!isDragging) {
        const zoomFactor = (currentDistance / initialPinchDistance);
        setZoom(e, zoomFactor);
    }
}

function setZoom(e, zoomFactor) {
    hasZoomed = true;
    let eLoc = getEventLocation(e);
    if (e.touches) {
        eLoc = {
            x: (eLoc[0].x + eLoc[1].x) / 2,
            y: (eLoc[0].y + eLoc[1].y) / 2
        };
    }
    const xs = (eLoc.x - objLoc.x) / currentZoom;
    const ys = (eLoc.y - objLoc.y) / currentZoom;
    currentZoom = clamp((e.touches ? lastZoom : currentZoom) * zoomFactor, MIN_ZOOM, MAX_ZOOM);
    objLoc.x = eLoc.x - xs * currentZoom;
    objLoc.y = eLoc.y - ys * currentZoom;
    setTransform();
}

// Custom math functions
function clamp(number, min, max) {
    return Math.max(min, Math.min(number, max));
}

function euclideanDistance(a, b) {
    return Math.hypot((b.x - a.x), (b.y - a.y));
}
documentReady(function(){
    enablePanAndZoom();
});
// Enable the pan and zoom feature after the page has loaded
// document.onload = enablePanAndZoom();
</script>
<script>
(function(){

  if (!('ontouchstart' in window)) return;

  const element = document.getElementById("object-view");
  if (!element) return;

  const parent = element.parentElement;
  let objLoc = { x: 0, y: 0 };
  let currentZoom = 1;
  let lastZoom = 1;
  let isDragging = false;
  let dragStart = { x: 0, y: 0 };
  let initialPinchDistance = null;

  // Set styles
  parent.style.cursor = "grab";
  element.style.transformOrigin = "0 0";
  element.style.width = "100%";
  element.parentElement.style.overflow = "hidden";

  // Apply transform to the element
  function updateTransform() {
    element.style.transform = `translate(${objLoc.x}px, ${objLoc.y}px) scale(${currentZoom})`;
  }

  // Get event coordinates
  function getEventLocation(e) {
    if (e.touches && e.touches.length === 1) {
      return { x: e.touches[0].clientX, y: e.touches[0].clientY };
    } else if (e.touches && e.touches.length === 2) {
      return [
        { x: e.touches[0].clientX, y: e.touches[0].clientY },
        { x: e.touches[1].clientX, y: e.touches[1].clientY }
      ];
    }
    return { x: e.clientX, y: e.clientY };
  }

  // Calculate distance between two points
  function distance(a, b) {
    return Math.hypot(b.x - a.x, b.y - a.y);
  }

  // Clamp number to range
  function clamp(num, min, max) {
    return Math.min(max, Math.max(min, num));
  }

  // Touch Start
  function onTouchStart(e) {
    e.preventDefault();

    if (e.touches.length === 1) {
      isDragging = true;
      let loc = getEventLocation(e);
      dragStart.x = loc.x - objLoc.x;
      dragStart.y = loc.y - objLoc.y;
      parent.style.cursor = "grabbing";
    } else if (e.touches.length === 2) {
      isDragging = false;
      initialPinchDistance = distance(getEventLocation(e)[0], getEventLocation(e)[1]);
      // ✅ Don't set lastZoom here to avoid first-time glitch
    }
  }

  // Touch Move
  function onTouchMove(e) {
    e.preventDefault();

    if (e.touches.length === 1 && isDragging) {
      let loc = getEventLocation(e);
      objLoc.x = loc.x - dragStart.x;
      objLoc.y = loc.y - dragStart.y;
      updateTransform();
    } else if (e.touches.length === 2) {
      let [p1, p2] = getEventLocation(e);
      let newDist = distance(p1, p2);

      if (initialPinchDistance) {
        let diff = newDist - initialPinchDistance;

        // ✅ Ignore small changes to prevent jitter
        if (Math.abs(diff) < 5) return;

        // Calculate zoom factor, clamp to smooth it
        let zoomFactor = newDist / initialPinchDistance;
        zoomFactor = Math.min(Math.max(zoomFactor, 0.95), 1.05);

        let midpoint = {
          x: (p1.x + p2.x) / 2,
          y: (p1.y + p2.y) / 2
        };

        let xs = (midpoint.x - objLoc.x) / currentZoom;
        let ys = (midpoint.y - objLoc.y) / currentZoom;

        currentZoom = clamp(lastZoom * zoomFactor, 0.1, 5);

        objLoc.x = midpoint.x - xs * currentZoom;
        objLoc.y = midpoint.y - ys * currentZoom;

        updateTransform();

        // ✅ Only update these after first real zoom event
        initialPinchDistance = newDist;
        lastZoom = currentZoom;
      }
    }
  }

  // Touch End
  function onTouchEnd(e) {
    e.preventDefault();
    if (e.touches.length === 0) {
      isDragging = false;
      initialPinchDistance = null;
      parent.style.cursor = "grab";
    } else if (e.touches.length === 1) {
      let loc = getEventLocation(e);
      dragStart.x = loc.x - objLoc.x;
      dragStart.y = loc.y - objLoc.y;
      isDragging = true;
    }
  }

  // Add event listeners with passive: false
  parent.addEventListener("touchstart", onTouchStart, { passive: false });
  parent.addEventListener("touchmove", onTouchMove, { passive: false });
  parent.addEventListener("touchend", onTouchEnd, { passive: false });
  parent.addEventListener("touchcancel", onTouchEnd, { passive: false });

  // Optional: reset function
  window.resetMobileTransform = function() {
    objLoc = { x: 0, y: 0 };
    currentZoom = 1;
    lastZoom = 1;
    updateTransform();
  }
})();
</script>
