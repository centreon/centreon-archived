import html2canvas from 'html2canvas';

interface Props {
  element: HTMLElement;
  title: string;
}

const exportToPng = ({ element, title }: Props): Promise<void> => {
  return html2canvas(element).then((canvas) => {
    const canvasUrl = canvas.toDataURL('image/png;base64');

    const downloadLink = document.createElement('a');
    downloadLink.download = `${title}-${new Date().toISOString()}.png`;
    downloadLink.href = canvasUrl;

    downloadLink.click();
    downloadLink.remove();
  });
};

export default exportToPng;
