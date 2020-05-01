import * as React from 'react';

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

import { makeStyles } from '@material-ui/core';

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
import { getFormattedDateTime } from '../../../dateTime';

const useStyles = makeStyles({
  comment: {
    display: 'block',
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
  },
});

interface DowntimeDetails {
  author_name: string;
  is_fixed: boolean;
  start_time: string;
  end_time: string;
  comment: string;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const DowntimeDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const classes = useStyles();

  const columns = [
    {
      id: 'author',
      label: labelAuthor,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ author_name }): string => author_name,
      width: 100,
    },
    {
      id: 'is_fixed',
      label: labelFixed,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ is_fixed }): string => (is_fixed ? labelYes : labelNo),
      width: 100,
    },
    {
      id: 'start_time',
      label: labelStartTime,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ start_time }): string => getFormattedDateTime(start_time),
      width: 150,
    },
    {
      id: 'end_time',
      label: labelEndTime,
      type: TABLE_COLUMN_TYPES.string,
      getContent: ({ end_time }): string => getFormattedDateTime(end_time),
      width: 150,
    },

    {
      id: 'comment',
      label: labelComment,
      type: TABLE_COLUMN_TYPES.string,
      width: 250,
      getContent: ({ comment }: DowntimeDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
