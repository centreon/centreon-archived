import React from 'react';

import {
  labelAuthor,
  labelFixed,
  labelYes,
  labelNo,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../../translatedLabels';
import DetailsTable, { getFormattedDate, DetailsTableProps } from '.';

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
      label: labelAuthor,
      getFormattedString: ({ author_name }): string => author_name,
    },
    {
      label: labelFixed,
      getFormattedString: ({ is_fixed }): string =>
        is_fixed ? labelYes : labelNo,
    },
    {
      label: labelStartTime,
      getFormattedString: ({ start_time }): string =>
        getFormattedDate(start_time),
    },
    {
      label: labelEndTime,
      getFormattedString: ({ end_time }): string => getFormattedDate(end_time),
    },

    {
      label: labelComment,
      getFormattedString: ({ comment }): string => comment,
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
