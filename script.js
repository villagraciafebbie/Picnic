// PAGE NAVIGATION FUNCTION
function nextPage(pageNumber) {
    document.querySelectorAll(".page").forEach(page => {
        page.classList.remove("active");
    });
    
    const targetPage = document.getElementById(`page${pageNumber}`);
    if (targetPage) {
        targetPage.classList.add("active");
        
        // Hide reaction image if it's visible when changing pages
        const reactionImg = document.getElementById("reactionImg");
        if (reactionImg) {
            reactionImg.style.display = "none";
        }
        
        // Scroll to top just in case (for mobile)
        window.scrollTo(0, 0);
    }
}

// Function to show sad reaction image when NO is clicked
function showReactionImage() {
    const reactionImg = document.getElementById("reactionImg");
    if (reactionImg) {
        reactionImg.style.display = "block";
        
        // Auto-hide after 2.5 seconds
        setTimeout(() => {
            if (reactionImg) {
                reactionImg.style.display = "none";
            }
        }, 2500);
    }
}

// ========== YES BUTTON: go to page 2 ==========
document.querySelector(".yes-btn")?.addEventListener("click", () => {
    nextPage(2);
});

// ========== NO BUTTON: Show reaction image + moving effect with responsive bounds ==========
const noBtn = document.getElementById("noBtn");
const buttonGroup = document.querySelector(".button-group");

if (noBtn && buttonGroup) {
    let maxOffsetX = 0;
    let maxOffsetY = 0;
    
    function updateMaxBounds() {
        if (!buttonGroup || !noBtn) return;
        const containerRect = buttonGroup.getBoundingClientRect();
        const btnRect = noBtn.getBoundingClientRect();
        
        // Calculate available space for movement
        let maxLeft = containerRect.width - btnRect.width;
        let maxTop = containerRect.height - btnRect.height;
        
        maxOffsetX = Math.max(0, maxLeft);
        maxOffsetY = Math.max(0, maxTop);
        
        // Reset position if out of bounds (prevents button from disappearing)
        const currentLeft = noBtn.style.left;
        const currentTop = noBtn.style.top;
        if (currentLeft && parseInt(currentLeft) > maxOffsetX) {
            noBtn.style.left = `${Math.min(maxOffsetX, maxOffsetX)}px`;
        }
        if (currentTop && parseInt(currentTop) > maxOffsetY) {
            noBtn.style.top = `${Math.min(maxOffsetY, maxOffsetY)}px`;
        }
    }
    
    window.addEventListener("resize", () => {
        updateMaxBounds();
        // Reset position on resize to avoid weird placement
        if (noBtn.style.position === "absolute") {
            noBtn.style.left = "";
            noBtn.style.top = "";
            noBtn.style.right = "0";
            noBtn.style.position = "absolute";
        }
    });
    
    setTimeout(updateMaxBounds, 100);
    
    // Touch and mouse move effect for mobile + desktop
    const moveButton = (e) => {
        e.preventDefault();
        updateMaxBounds();
        
        if (maxOffsetX <= 8 && maxOffsetY <= 8) {
            noBtn.style.transform = "translate(5px, 5px)";
            setTimeout(() => { noBtn.style.transform = ""; }, 150);
            return;
        }
        
        let randomX = Math.floor(Math.random() * (maxOffsetX + 1));
        let randomY = Math.floor(Math.random() * (maxOffsetY + 1));
        
        randomX = Math.min(randomX, maxOffsetX);
        randomY = Math.min(randomY, maxOffsetY);
        
        noBtn.style.left = `${randomX}px`;
        noBtn.style.top = `${randomY}px`;
        noBtn.style.right = "auto";
        noBtn.style.position = "absolute";
        
        noBtn.style.transform = "scale(0.97)";
        setTimeout(() => {
            if (noBtn) noBtn.style.transform = "";
        }, 120);
    };
    
    noBtn.addEventListener("mouseover", moveButton);
    // For touch devices: move when touched (but not click)
    noBtn.addEventListener("touchstart", (e) => {
        e.preventDefault();
        moveButton(e);
    });
    
    // NO BUTTON CLICK: Show reaction image + alert + move again
    noBtn.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        
        // SHOW THE SAD/REACTION IMAGE
        showReactionImage();
        
        alert("Haha! No escape? But sorry, you can't say NO! 😜 Just kidding, but click YES na! 🧡");
        
        // Move button again after click for fun
        updateMaxBounds();
        if (maxOffsetX > 10) {
            const randomX = Math.floor(Math.random() * maxOffsetX);
            const randomY = Math.floor(Math.random() * maxOffsetY);
            noBtn.style.left = `${randomX}px`;
            noBtn.style.top = `${randomY}px`;
        }
    });
}

// ========== NEXT BUTTON goes to page 3 ==========
document.getElementById("nextBtn")?.addEventListener("click", () => {
    nextPage(3);
});

// ========== DATE BUTTON (validate date & time) ==========
document.getElementById("dateBtn")?.addEventListener("click", () => {
    const dateInput = document.querySelector("input[name='date']");
    const timeSelect = document.querySelector("select[name='time']");
    
    if (!dateInput.value) {
        alert("Huy! Pumili ka muna ng date! 🗓️");
        dateInput.focus();
        return;
    }
    if (!timeSelect.value) {
        alert("Pili ka rin ng time para masaya! ⏰");
        timeSelect.focus();
        return;
    }
    
    // proceed to page 4
    nextPage(4);
});

// ========== COLOR SELECTION with validation ==========
const colorBoxes = document.querySelectorAll(".color-box");
let selectedColor = null;
const colorErrorMsg = document.getElementById("colorError");

colorBoxes.forEach(box => {
    box.addEventListener("click", () => {
        // Remove selected class from all
        colorBoxes.forEach(item => {
            item.classList.remove("selected");
        });
        // Add selected class to clicked box
        box.classList.add("selected");
        selectedColor = box.dataset.color;
        
        // Hide error message if it was showing
        if (colorErrorMsg) {
            colorErrorMsg.style.display = "none";
        }
        
        // Haptic feedback on mobile (vibration if supported)
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(50);
        }
    });
});

// ========== SUBMIT BUTTON (validate color + send to PHP backend) ==========
document.getElementById("submitBtn")?.addEventListener("click", () => {
    // VALIDATION: Check if user picked a color
    if (!selectedColor) {
        if (colorErrorMsg) {
            colorErrorMsg.style.display = "block";
            // Shake error message for attention
            colorErrorMsg.style.animation = "shake 0.3s ease-in-out";
            setTimeout(() => {
                if (colorErrorMsg) colorErrorMsg.style.animation = "";
            }, 300);
        } else {
            alert("Pili ka muna color para may ambag!😒");
        }
        return;
    }
    
    const form = document.getElementById("picnicForm");
    if (!form) return;
    
    const formData = new FormData(form);
    formData.append("color", selectedColor);
    
    // Show loading state
    const submitBtn = document.getElementById("submitBtn");
    const originalText = submitBtn.innerText;
    submitBtn.innerText = "Sending...";
    submitBtn.disabled = true;
    
    // Send to PHP backend
    fetch("index.php", {
        method: "POST",
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server response:", data);
        // Proceed to final page on success
        nextPage(5);
    })
    .catch(error => {
        console.log("Error or no backend response, continuing to picnic!", error);
        // Even if PHP fails, proceed to page 5
        nextPage(5);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.innerText = originalText;
            submitBtn.disabled = false;
        }
    });
});

// ========== Girl character easter egg ==========
document.querySelectorAll(".girl").forEach(girl => {
    girl.addEventListener("click", () => {
        console.log("🐣 You touched the picnic buddy!");
        girl.style.transform = "scale(1.03)";
        setTimeout(() => {
            girl.style.transform = "";
        }, 200);
        // Small vibration on touch for fun
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(30);
        }
    });
    
    // Touch end reset for smoother interaction
    girl.addEventListener("touchend", () => {
        setTimeout(() => {
            girl.style.transform = "";
        }, 150);
    });
});

// ========== Ensure speech bubble is visible ==========
const styleCheck = () => {
    const bubble = document.getElementById("speechBubble");
    if (bubble) {
        bubble.classList.add("speech-bubble");
    }
};
setTimeout(styleCheck, 100);

// Additional responsive helper: when orientation changes, recalc button bounds
window.addEventListener("orientationchange", () => {
    setTimeout(() => {
        if (noBtn && buttonGroup) {
            if (noBtn.style.position === "absolute") {
                // reset to default right position to avoid overflow
                noBtn.style.left = "";
                noBtn.style.top = "";
                noBtn.style.right = "0";
            }
            const event = new Event('resize');
            window.dispatchEvent(event);
        }
    }, 100);
});