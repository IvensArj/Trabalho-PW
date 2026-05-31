const canvas = document.getElementById("mouse-trail");

if (!canvas) {
    throw new Error("Canvas #mouse-trail não encontrado.");
}

const ctx = canvas.getContext("2d");

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}

resizeCanvas();
window.addEventListener("resize", resizeCanvas);

const strokes = [];

let drawing = false;
let currentStroke = null;

const WAIT_BEFORE_FADE = 1500;
const FADE_DURATION = 800;

let lastDrawTime = Date.now();

let globalFadeStarted = false;
let fadeStartTime = 0;
let globalFadeProgress = 0;

document.addEventListener("mousedown", (e) => {

    if (e.button !== 0) return;

    drawing = true;

    currentStroke = {
        points: []
    };

    currentStroke.points.push({
        x: e.clientX,
        y: e.clientY
    });

    strokes.push(currentStroke);

    globalFadeStarted = false;
    globalFadeProgress = 0;
    lastDrawTime = Date.now();
});

document.addEventListener("mouseup", () => {
    drawing = false;
    currentStroke = null;
});

document.addEventListener("mousemove", (e) => {

    if (!drawing || !currentStroke) return;

    // 🚫 bloqueia áreas com classe
    if (isInsideBlockedArea(e.clientX, e.clientY)) return;

    currentStroke.points.push({
        x: e.clientX,
        y: e.clientY
    });

    lastDrawTime = Date.now();

    globalFadeStarted = false;
    globalFadeProgress = 0;
});
function drawStroke(stroke) {

    const points = stroke.points;

    if (points.length < 2) return;

    let visiblePoints = points.length;

    if (globalFadeStarted) {

        const eased = Math.min(
            1,
            globalFadeProgress
        );

        visiblePoints = Math.max(
            2,
            Math.floor(points.length * (1 - eased))
        );
    }

    ctx.beginPath();

    ctx.moveTo(
        points[0].x,
        points[0].y
    );

    for (
        let i = 1;
        i < visiblePoints - 1;
        i++
    ) {

        const current = points[i];
        const next = points[i + 1];

        const midX =
            (current.x + next.x) / 2;

        const midY =
            (current.y + next.y) / 2;

        ctx.quadraticCurveTo(
            current.x,
            current.y,
            midX,
            midY
        );
    }

    const last =
        points[visiblePoints - 1];

    ctx.lineTo(
        last.x,
        last.y
    );

    let width = 4;

    if (globalFadeStarted) {
        width *= Math.max(
            0.05,
            1 - globalFadeProgress
        );
    }

    ctx.lineWidth = width;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";
    ctx.strokeStyle = "#444";

    ctx.stroke();
}

function animate() {

    ctx.clearRect(
        0,
        0,
        canvas.width,
        canvas.height
    );

    const now = Date.now();

    if (
        !globalFadeStarted &&
        now - lastDrawTime > WAIT_BEFORE_FADE
    ) {
        globalFadeStarted = true;
        fadeStartTime = now;
    }

    if (globalFadeStarted) {

        const elapsed = now - fadeStartTime;

        globalFadeProgress = Math.min(
            1,
            elapsed / FADE_DURATION
        );

        if (globalFadeProgress >= 1) {
            strokes.length = 0;
            globalFadeStarted = false;
            globalFadeProgress = 0;
        }
    }

    for (const stroke of strokes) {
        drawStroke(stroke);
    }

    requestAnimationFrame(animate);
}

function isInsideBlockedArea(x, y) {
    const el = document.elementFromPoint(x, y);

    if (!el) return false;

    return el.closest(".no-trail");
}

animate();