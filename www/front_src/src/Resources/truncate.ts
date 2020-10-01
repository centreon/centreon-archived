import { gt } from 'ramda';

const truncate = (content: string): string => {
  const maxLength = 180;

  if (gt(content.length, maxLength)) {
    return `${content.substring(0, maxLength)}...`;
  }

  return content;
};

export default truncate;
