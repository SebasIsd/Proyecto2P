document.addEventListener('DOMContentLoaded', () => {
    fetch('php/home_content.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('home-title').textContent = data.titulo;
            document.getElementById('home-content').textContent = data.contenido;
            if (data.imagen) {
                document.getElementById('home-image').src = data.imagen;
                document.getElementById('home-image').style.display = 'block';
            }
        })
        .catch(error => console.error('Error fetching home content:', error));
});