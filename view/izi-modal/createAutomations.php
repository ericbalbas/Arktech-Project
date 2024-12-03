<?php
?>
<div class="container-fluid">
    <form action="index.php?route=create/automations" method="post" enctype="multipart/form-data">
        <div class="row mb-2">
            <div class="col-md-6">
                <label for="nestingDrawing" class="form-label">Nesting Drawing (PDF):</label>
                <input type="file" name="nestingDrawing" id="nestingDrawing" accept=".pdf" class="form-control shadow-sm">
            </div>
            <div class="col-md-6">
                <label for="nestingProgramInput" class="form-label">Nesting Program (ZIP):</label>
                <input type="file" name="nestingProgram" id="nestingProgramInput" accept=".001" class="form-control shadow-sm">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="partNumber" class="form-label">Part Name:</label>
                <input required type="text" class="form-control" placeholder="Part Name" name="partNumber" id="partNumber" datalist="">
            </div>
            <div class="col-md-4">
                <label for="materialName" class="form-label">Material:</label>
                <input type="text" class="form-control" placeholder="Material" name="materialName" id="materialName">
            </div>
            <div class="col-md-4">
                <label for="sheetQuantity" class="form-label">Sheet Quantity:</label>
                <input type="number" class="form-control" placeholder="Quantity" name="sheetQuantity" id="sheetQuantity" datalist="">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <label for="materialThickness" class="form-label">Thickness:</label>
                <input required type="number" id="materialThickness" name="materialThickness" step="0.01" class="form-control" placeholder="e.g., 12.34">
            </div>
            <div class="col-md-4">
                <label for="materialSize" class="form-label">Size:</label>
                <div class="row">
                    <div class="col-md-6"><input required placeholder="Height" class="form-control" type="number" name="materialHeight" id="materialHeight"></div>
                    <div class="col-md-6"><input required placeholder="Width" class="form-control" type="number" name="materialWidth" id="materialWidth"></div>
                </div>
            </div>
            <div class="col-md-4">
                <label for="cuttingCondition" class="form-label">Cutting Condition:</label>
                <input required type="text" placeholder="Cutting Condition" name="cuttingCondition" class="form-control" id="cuttingCondition">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label for="Machine" class="form-label">Machine:</label>
                <input type="text" class="form-control" placeholder="machine" name="machine" id="Machine">
            </div>
            <div class="col-md-4">
                <label for="processTime" class="form-label">Processing Time:</label>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input type="text" name="processTime" id="processTime" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary float-end">Create Automation</button>
            </div>
        </div>
    </form>

</div>