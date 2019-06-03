const fs = require('fs');
const archiver = require('archiver');

const output = fs.createWriteStream('pack/itsttangointegracion.zip');
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
