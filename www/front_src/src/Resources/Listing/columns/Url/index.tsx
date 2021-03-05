import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';

import { IconButton } from '@centreon/ui';

import { labelUrl } from '../../../translatedLabels';

interface Props {
  endpoint?: string;
  title?: string;
  icon: JSX.Element;
}

const UrlColumn = ({ endpoint, title, icon }: Props): JSX.Element | null => {
  const { t } = useTranslation();

  if (isNil(endpoint) || isEmpty(endpoint)) {
    return null;
  }

  return (
    <a
      href={endpoint}
      onClick={(e): void => {
        e.stopPropagation();
      }}
    >
      <IconButton
        title={t(title || labelUrl)}
        ariaLabel={title}
        onClick={(): null => {
          return null;
        }}
      >
        {icon}
      </IconButton>
    </a>
  );
};

export default UrlColumn;
