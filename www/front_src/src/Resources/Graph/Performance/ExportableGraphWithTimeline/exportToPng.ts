import { saveAs } from 'file-saver';
import dom2image from 'dom-to-image';

interface Props {
  backgroundColor: string;
  element: HTMLElement;
  ratio: number;
  title: string;
}

const exportToPng = async ({
  element,
  title,
  ratio,
  backgroundColor
}: Props): Promise<void> => {
  const dateTime = new Date().toISOString().substring(0, 19);

  const getTranslation = (size: number): number => {
    return ((1 - ratio) * size) / 2;
  };

  const translateY = getTranslation(element.offsetHeight);
  const translateX = getTranslation(element.offsetWidth);

  return dom2image
    .toBlob(element, {
      bgcolor: backgroundColor,
      height: element.offsetHeight * ratio,
      style: {
        transform: `translate(-${translateX}px, -${translateY}px) scale(${ratio})`
      },
      width: element.offsetWidth * ratio
    })
    .then((blob) => {
      return saveAs(blob, `${title}-${dateTime}.png`);
    });
};

export default exportToPng;
