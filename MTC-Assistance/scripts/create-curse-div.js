function AddCurseDiv() {
    if (document.querySelector(".curse-div")) {
        return;
    }

    const main = document.getElementById("main");
    const div = document.createElement("div");
    div.className = "curse-div"; 
    div.innerHTML = `
    <form id="curse-form">
        <label>Ingresa nombre del área</label>
        <input type="text" id="curse-name" name="curse-name" placeholder="Nombre del área" required>
        <button type="submit">Crear</button>
    </form>`;
    
    const form = div.querySelector('#curse-form');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const curseName = document.getElementById('curse-name').value;
        
        try {
            const response = await fetch('process_curse.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `curse_name=${encodeURIComponent(curseName)}`
            });

            const data = await response.json();
            if(data.success) {
                createAreaContainer(curseName);
                RemoveCurseDiv();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    main.appendChild(div);
}

function RemoveCurseDiv() {
    const curseDiv = document.querySelector(".curse-div");
    if (curseDiv) {
        curseDiv.remove();
    }
}

function createAreaContainer(areaName) {
    const main = document.getElementById("main");
    const areaContainer = document.createElement("div");
    areaContainer.className = "area-container";
    areaContainer.innerHTML = `
        <h3>${areaName}</h3>
        <div class="area-content">
            <!-- Aquí puedes agregar más contenido del área -->
        </div>
    `;
    main.appendChild(areaContainer);
}
