import React from 'react';

import { TABLE_COLUMN_TYPES } from '@centreon/ui';

import {
  labelAuthor,
  labelFixed,
  labelYes,
  labelNo,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../../translatedLabels';
import DetailsTable, { DetailsTableProps } from '.';
import getFormattedDate from '../../../getFormattedDate';

interface DowntimeDetails {
  author_name: string;
  is_fixed: boolean;
  start_time: string;
  end_time: string;
  ccoment: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const DowntimeDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const columns = [
    {
      id: 'author',
      label: labelAuthor,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ author_name }): string => author_name,
    },
    {
      id: 'is_fixed',
      label: labelFixed,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ is_fixed }): string =>
        is_fixed ? labelYes : labelNo,
    },
    {
      id: 'start_time',
      label: labelStartTime,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ start_time }): string =>
        getFormattedDate(start_time),
    },
    {
      id: 'end_time',
      label: labelEndTime,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ end_time }): string => getFormattedDate(end_time),
    },

    {
      id: 'comment',
      label: labelComment,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ comment }): string => comment,
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
