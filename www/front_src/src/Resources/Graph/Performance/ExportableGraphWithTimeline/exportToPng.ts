import { saveAs } from 'file-saver';
import dom2image from 'dom-to-image';

interface Props {
  element: HTMLElement;
  title: string;
}

const exportToPng = async ({ element, title }: Props): Promise<void> => {
  const dateTime = new Date().toISOString().substring(0, 19);

  return dom2image
    .toBlob(element, {
      bgcolor: '#FFFFFF',
    })
    .then((blob) => {
      return saveAs(blob, `${title}-${dateTime}.png`);
    });
};

export default exportToPng;
