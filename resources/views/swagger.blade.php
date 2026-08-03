<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Task Management API Documentation</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js" crossorigin></script>
<script>
    window.addEventListener('load', () => {
        window.ui = SwaggerUIBundle({
            url: @json(route('docs.openapi')),
            dom_id: '#swagger-ui',
            deepLinking: true,
            persistAuthorization: true,
            presets: [SwaggerUIBundle.presets.apis],
        });
    });
</script>
</body>
</html>
