import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';

import { IconButton } from '@centreon/ui';

import { labelUrl } from '../../../translatedLabels';
import IconColumn from '../IconColumn';

interface Props {
  endpoint?: string;
  icon: JSX.Element;
  title?: string;
}

const UrlColumn = ({ endpoint, title, icon }: Props): JSX.Element | null => {
  const { t } = useTranslation();

  if (isNil(endpoint) || isEmpty(endpoint)) {
    return null;
  }

  return (
    <IconColumn>
      <a
        href={endpoint}
        onClick={(e): void => {
          e.stopPropagation();
        }}
      >
        <IconButton
          ariaLabel={title}
          title={t(title || labelUrl)}
          onClick={(): null => {
            return null;
          }}
        >
          {icon}
        </IconButton>
      </a>
    </IconColumn>
  );
};

export default UrlColumn;
