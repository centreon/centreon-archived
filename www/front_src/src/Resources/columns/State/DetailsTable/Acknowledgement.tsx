import * as React from 'react';

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

import { makeStyles } from '@material-ui/core';

import { TABLE_COLUMN_TYPES } from '@centreon/ui';

import {
  labelAuthor,
  labelComment,
  labelEntryTime,
  labelPersistent,
  labelSticky,
} from '../../../translatedLabels';
import DetailsTable, { DetailsTableProps, getYesNoLabel } from '.';
import { getFormattedDateTime } from '../../../dateTime';

const commentWidth = 500;

const useStyles = makeStyles({
  comment: {
    display: 'block',
    maxWidth: commentWidth,
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
  },
});

interface AcknowledgementDetails {
  author_name: string;
  entry_time: string;
  is_persistent_comment: string;
  is_sticky: string;
  comment: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const AcknowledgementDetailsTable = ({ loading, data }: Props): JSX.Element => {
  const classes = useStyles();
  console.log(data)

  const columns = [
    {
      id: 'author',
      label: labelAuthor,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ author_name }): string => author_name,
    },
    {
      id: 'entry_time',
      label: labelEntryTime,
      width: 150,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ entry_time }): string => getFormattedDateTime(entry_time),
    },
    {
      id: 'is_persistent',
      label: labelPersistent,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ is_persistent_comment }): string =>
        getYesNoLabel(is_persistent_comment),
    },
    {
      id: 'is_sticky',
      label: labelSticky,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ is_sticky }): string => getYesNoLabel(is_sticky),
    },

    {
      id: 'comment',
      label: labelComment,
      width: commentWidth,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ comment }: AcknowledgementDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
    },
  ];

  return (
    <DetailsTable<AcknowledgementDetails>
      columns={columns}
      loading={loading}
      data={data}
    />
  );
};

export default AcknowledgementDetailsTable;
