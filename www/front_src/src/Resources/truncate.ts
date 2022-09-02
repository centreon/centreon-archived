import { gt, isNil } from 'ramda';

const truncate = (content?: string): string => {
  const maxLength = 180;

  if (isNil(content)) {
    return '';
  }

  if (gt(content.length, maxLength)) {
    return `${content.substring(0, maxLength)}...`;
  }

  return content;
};

export default truncate;
