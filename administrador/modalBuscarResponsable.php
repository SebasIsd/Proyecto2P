<div class="modal fade" id="modalBuscar">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">

    <div class="modal-header">
        <h5 class="modal-title">Buscar Responsable</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>

    <div class="modal-body">
        <input type="search" id="termBuscar" class="form-control mb-3"
               placeholder="Buscar por cédula, nombre o apellido">

        <table class="table table-hover table-sm">
            <thead class="table-secondary">
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tblResultados">
                <tr><td colspan="4" class="text-center text-muted">Escriba para buscar...</td></tr>
            </tbody>
        </table>
    </div>

</div>
</div>
</div>

<script>
// 🔍 Buscador AJAX
let timer=null;
$("#termBuscar").on("input",function(){
    const q = this.value.trim();
    clearTimeout(timer);
    if(q.length < 2){
        $("#tblResultados").html('<tr><td colspan="4" class="text-muted text-center">Escriba 2 letras...</td></tr>');
        return;
    }
    timer = setTimeout(()=>buscar(q),300);
});

function buscar(q){
    $("#tblResultados").html('<tr><td colspan="4" class="text-center">Buscando...</td></tr>');
    $.getJSON("buscarResponsable.php",{term:q},function(data){
        if(!data.length){
            $("#tblResultados").html('<tr><td colspan="4" class="text-danger text-center">Sin resultados</td></tr>');
            return;
        }
        $("#tblResultados").html(
            data.map(u => `
                <tr>
                    <td>${u.cedula}</td>
                    <td>${u.nombreCompleto}</td>
                    <td>${u.correo}</td>
                    <td>
                        <button class="btn btn-success btn-sm seleccionar"
                                data-ced="${u.cedula}"
                                data-nombre="${u.nombreCompleto}">
                            Elegir
                        </button>
                    </td>
                </tr>
            `).join("")
        );
    });
}

// Seleccionar
$(document).on("click",".seleccionar",function(){
    const ced = $(this).data("ced");
    const nombre = $(this).data("nombre");

    $("#RESPONSABLE_CED").val(ced);
    $("#responsable_display").val(`${ced} - ${nombre}`);
    $("#responsableNombre").text(nombre);

    bootstrap.Modal.getInstance(document.getElementById("modalBuscar")).hide();
});
</script>
