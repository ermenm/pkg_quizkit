const fs = require('fs-extra');
const archiver = require('archiver');
const path = require('path');

// Definieer de submappen in de 'packages' map
const packagesFolder = 'packages';
const subFolders = ['mod_quizmaker', 'mod_quizdashboard'];

async function deleteExistingZips(folder) {
    const files = await fs.readdir(folder);
    for (const file of files) {
        if (file.endsWith('.zip')) {
            await fs.remove(path.join(folder, file));
        }
    }
}

async function zipFolder(folderPath, zipPath) {
    return new Promise((resolve, reject) => {
        const output = fs.createWriteStream(zipPath);
        const archive = archiver('zip', {
            zlib: { level: 9 } // Maximale compressie
        });

        output.on('close', () => resolve());
        archive.on('error', (err) => reject(err));

        archive.pipe(output);
        archive.directory(folderPath, false);
        archive.finalize();
    });
}

async function main() {
    try {
        // Verwijder bestaande zip-bestanden in de 'packages' map
        await deleteExistingZips(packagesFolder);

        // Zip de submappen
        for (const subFolder of subFolders) {
            const folderPath = path.join(packagesFolder, subFolder);
            const zipPath = path.join(packagesFolder, `${subFolder}.zip`);
            await zipFolder(folderPath, zipPath);
        }

        console.log('Zip process completed successfully.');
    } catch (error) {
        console.error('An error occurred:', error);
    }
}

main();
