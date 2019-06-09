const fs = require('fs');
const archiver = require('archiver');

const rawPack = fs.readFileSync("./package.json");
const packData = JSON.parse(rawPack.toString());

const path = `pack/${packData.version}`;
if (!fs.existsSync(path)) {
  fs.mkdirSync(path);
}

const output = fs.createWriteStream(`${path}/itsttangointegracion.zip`);
const archive = archiver('zip');

output.on('close', () => {
  console.log(archive.pointer() + ' total bytes');
  console.log('archiver has been finalized and the output file descriptor has closed.');
});

archive.on('error', (err) => {
  throw err;
});

archive.pipe(output);
archive.directory('itsttangointegracion/', 'itsttangointegracion');
archive.finalize();
