function renderFlowchart(states, options = {}) {
    const canvas = (typeof options.container === 'string') ? document.querySelector(options.container) : options.container;
    if (!canvas) throw new Error('No se encontró el canvas');
    const ctx = canvas.getContext('2d');

    const nodeW = options.nodeWidth || 160;
    const nodeH = options.nodeHeight || 56;
    const colGap = options.colGap || 220;
    const rowGap = options.rowGap || 110;
    const pad = options.padding || 40;

    // --- Indexar
    const byId = new Map();
    states.forEach(s => byId.set(s.id, { ...s, prev: s.prev || [] }));

    // --- Grafo
    const outEdges = new Map();
    const inDegree = new Map();
    states.forEach(s => { inDegree.set(s.id, s.prev.length); outEdges.set(s.id, new Set()); });
    states.forEach(s => s.prev.forEach(p => outEdges.get(p).add(s.id)));

    // --- Niveles (Kahn)
    const queue = [];
    const level = new Map();
    inDegree.forEach((deg, id) => { if (deg === 0) { queue.push(id); level.set(id, 0); } });
    while (queue.length) {
        const u = queue.shift();
        const nextLevel = (level.get(u) || 0) + 1;
        (outEdges.get(u) || []).forEach(v => {
            level.set(v, Math.max(level.get(v) ?? 0, nextLevel));
            inDegree.set(v, inDegree.get(v) - 1);
            if (inDegree.get(v) === 0) queue.push(v);
        });
    }

    // --- Agrupar por columnas
    const columns = new Map();
    level.forEach((lvl, id) => { if (!columns.has(lvl)) columns.set(lvl, []); columns.get(lvl).push(id); });
    columns.forEach(arr => arr.sort());

    // --- Calcular tamaño requerido y habilitar scroll (canvas más grande que la ventana)
    const colCount = Math.max(...columns.keys()) + 1;
    let maxRows = 0; columns.forEach(arr => { if (arr.length > maxRows) maxRows = arr.length; });
    const requiredWidth = pad * 2 + colCount * nodeW + (colCount - 1) * colGap;
    const requiredHeight = pad * 2 + maxRows * nodeH + (maxRows - 1) * rowGap;

    // Si el contenido es mayor que la pantalla, el body mostrará scrollbars
    const viewportW = canvas.clientWidth || window.innerWidth;
    const viewportH = canvas.clientHeight || (window.innerHeight - 56);
    canvas.width = Math.max(requiredWidth, viewportW);
    canvas.height = Math.max(requiredHeight, viewportH);

    // --- Limpiar
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // --- Posicionar nodos
    const pos = new Map();
    columns.forEach((ids, lvl) => {
        const totalH = ids.length * nodeH + (ids.length - 1) * rowGap;
        const startY = Math.max(pad, (canvas.height - totalH) / 2);
        const x = pad + lvl * (nodeW + colGap);
        ids.forEach((id, i) => pos.set(id, { x, y: startY + i * (nodeH + rowGap) }));
    });

    // --- Conectores
    ctx.strokeStyle = '#94a3b8';
    ctx.lineWidth = 1.25;
    ctx.fillStyle = '#94a3b8';
    states.forEach(s => {
        const target = pos.get(s.id);
        s.prev.forEach(p => {
            const source = pos.get(p);
            const x1 = source.x + nodeW;
            const y1 = source.y + nodeH / 2;
            const x2 = target.x;
            const y2 = target.y + nodeH / 2;
            ctx.beginPath();
            ctx.moveTo(x1, y1);
            const dx = Math.max(40, (x2 - x1) * 0.5);
            ctx.bezierCurveTo(x1 + dx, y1, x2 - dx, y2, x2, y2);
            ctx.stroke();
            // Flecha
            ctx.beginPath();
            ctx.moveTo(x2 - 8, y2 - 5);
            ctx.lineTo(x2, y2);
            ctx.lineTo(x2 - 8, y2 + 5);
            ctx.fill();
        });
    });

    // --- Nodos
    states.forEach(s => {
        const { x, y } = pos.get(s.id);
        ctx.fillStyle = s.type === 'start' ? '#ecfdf5' : s.type === 'end' ? '#fef2f2' : '#f8fafc';
        ctx.strokeStyle = s.type === 'start' ? '#34d399' : s.type === 'end' ? '#f87171' : '#94a3b8';
        ctx.lineWidth = 1.5;
        if (ctx.roundRect) {
            ctx.beginPath();
            ctx.roundRect(x, y, nodeW, nodeH, 10);
            ctx.fill();
            ctx.stroke();
        } else {
            // Fallback sin roundRect
            ctx.fillRect(x, y, nodeW, nodeH);
            ctx.strokeRect(x, y, nodeW, nodeH);
        }

        ctx.fillStyle = '#111827';
        ctx.font = '13px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(s.label || s.id, x + nodeW / 2, y + nodeH / 2);
    });
}