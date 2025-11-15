import { readFileSync, writeFileSync } from 'fs';

const templatePath = process.argv[2];
const outputPath = process.argv[3];

let template = readFileSync(templatePath, 'utf-8');

// Replace ${PORT} with actual PORT environment variable
const port = process.env.PORT || '80';
template = template.replace(/\${PORT}/g, port);

// Replace ${SERVER_NAME} if provided
if (process.env.SERVER_NAME) {
  template = template.replace(/server_name _;/g, `server_name ${process.env.SERVER_NAME};`);
}

writeFileSync(outputPath, template);

