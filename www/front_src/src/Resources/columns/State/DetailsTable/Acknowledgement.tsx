import React from 'react';

import { TABLE_COLUMN_TYPES } from '@centreon/ui';

import {
  labelAuthor,
  labelComment,
  labelEntryTime,
  labelPersistent,
  labelSticky,
} from '../../../translatedLabels';
import DetailsTable, { DetailsTableProps, getYesNoLabel } from '.';
import getFormattedDate from '../../../getFormattedDate';

interface AcknoweldgementDetails {
  author_name: string;
  entry_time: string;
  is_persistent_comment: string;
  is_sticky: string;
  comment: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const AcknowledgementDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const columns = [
    {
      id: 'author',
      label: labelAuthor,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ author_name }): string => author_name,
    },
    {
      id: 'entry_time',
      label: labelEntryTime,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ entry_time }): string =>
        getFormattedDate(entry_time),
    },
    {
      id: 'is_persistent',
      label: labelPersistent,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ is_persistent_comment }): string =>
        getYesNoLabel(is_persistent_comment),
    },
    {
      id: 'is_sticky',
      label: labelSticky,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ is_sticky }): string => getYesNoLabel(is_sticky),
    },

    {
      id: 'comment',
      label: labelComment,
      type: TABLE_COLUMN_TYPES.string,
      getFormattedString: ({ comment }): string => comment,
    },
  ];

  return (
    <DetailsTable<AcknoweldgementDetails>
      columns={columns}
      endpoint={endpoint}
    />
  );
};

export default AcknowledgementDetailsTable;
