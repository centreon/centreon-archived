import React from 'react';

import {
  labelAuthor,
  labelComment,
  labelEntryTime,
  labelPersistent,
  labelSticky,
} from '../../../translatedLabels';
import DetailsTable, {
  getFormattedDate,
  DetailsTableProps,
  getYesNoLabel,
} from '.';

interface AcknoweldgementDetails {
  author_name: string;
  entry_time: string;
  is_persistent: string;
  is_sticky: string;
  comment: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const AcknowledgementDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const columns = [
    {
      label: labelAuthor,
      getFormattedString: ({ author_name }): string => author_name,
    },
    {
      label: labelEntryTime,
      getFormattedString: ({ entry_time }): string =>
        getFormattedDate(entry_time),
    },
    {
      label: labelPersistent,
      getFormattedString: ({ is_persistent }): string =>
        getYesNoLabel(is_persistent_comment),
    },
    {
      label: labelSticky,
      getFormattedString: ({ is_sticky }): string => getYesNoLabel(is_sticky),
    },

    {
      label: labelComment,
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
