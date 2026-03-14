import { Injectable } from '@nestjs/common';
import sharp from 'sharp';
import * as path from 'path';
import * as fs from 'fs/promises';

@Injectable()
export class ImageService {
  async convertToJpeg(filePath: string): Promise<string> {
    const ext = path.extname(filePath).toLowerCase();
    const directory = path.dirname(filePath);
    const baseName = path.basename(filePath, ext);
    
    // Always output to a file with .jpg extension
    const outputFileName = `${baseName}_converted.jpg`;
    const outputPath = path.join(directory, outputFileName);

    try {
      // Read original file into buffer to avoid lock issues if same filename
      const buffer = await fs.readFile(filePath);
      
      await sharp(buffer)
        .jpeg({ quality: 80 })
        .toFile(outputPath);

      // Always delete the original file (since it was uploaded by multer as a temp)
      try {
        await fs.unlink(filePath);
      } catch (err) {
        console.error(`Failed to delete original file: ${filePath}`, err);
      }

      return outputPath;
    } catch (error) {
      console.error('Error converting image to JPEG:', error);
      throw error;
    }
  }
}
